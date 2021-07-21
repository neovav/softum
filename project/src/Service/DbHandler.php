<?php

namespace App\Service;

use PHPSQLParser\PHPSQLParser;

class DbHandler
{
    private static array $listRemoveTags = [
        'a',
        '/a',
        'img'
    ];

    /**
     * Getting list posts from DB wordpress file
     *
     * @param string $path
     *
     * @return array
     *
     * @throws \Exception
     */
    public function listPosts(string $path) :array
    {
        $result = [];

        if (!file_exists($path)) {
            throw new \Exception('Данный файл отсутствует или неверный путь');
        }

        $sqlLists = explode("\n", file_get_contents($path));

        if (is_array($sqlLists) && !empty($sqlLists)) {
            $listRows = self::listInsertPosts($sqlLists, $sqlHeader);

            if (!empty($sqlHeader) && !empty($listRows)) {
                $parser = new PHPSQLParser($sqlHeader . implode("\n", $listRows), true);

                if ($parser instanceof PHPSQLParser) {
                    $data = $parser->parsed;
                    if (is_array($data)
                        && !empty($data['INSERT'])
                        && !empty($data['INSERT'][2])
                        && !empty($data['INSERT'][2]['sub_tree'])
                        && !empty($data['VALUES'])
                        && is_array($data['VALUES'])) {

                        self::searchNumCells ($data['INSERT'][2]['sub_tree'], $cellNumTitle, $cellNumContent);
                        if (!is_null($cellNumTitle) && !is_null($cellNumContent)) {
                            $countRecords = count($data['VALUES']);
                            for ($i = 0; $i < $countRecords; $i++) {
                                $record = $data['VALUES'][$i];
                                if (empty($record['data'])) {
                                    continue;
                                }

                                $result[] = self::getDataFromArray($record['data'], $cellNumTitle, $cellNumContent);
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Search number cells with title and content data for posts
     *
     * @param array $data
     * @param int|null $cellNumTitle
     * @param int|null $cellNumContent
     */
    private static function searchNumCells (array $data, int &$cellNumTitle = null, int &$cellNumContent = null)
    {
        $countFields = count($data);
        for ($i = 0; $i < $countFields; $i++) {
            $item = $data[$i];
            if (!empty($item['base_expr'])) {
                switch ($item['base_expr']) {
                    case '`post_title`' :
                    case 'post_title' :
                        $cellNumTitle = $i;
                        break;
                    case '`post_content`' :
                    case 'post_content' :
                        $cellNumContent = $i;
                        break;
                }
            }
        }
    }

    /**
     * Getting data (title and content) from array
     *
     * @param array $record
     * @param int $cellNumTitle
     * @param int $cellNumContent
     *
     * @return array
     */
    private static function getDataFromArray(array $record, int $cellNumTitle, int $cellNumContent) :array
    {

        $title = '';
        $content = '';
        if (!empty($record[$cellNumTitle])) {
            $title = self::removeQuotes($record[$cellNumTitle]['base_expr']);
        }
        if (!empty($record[$cellNumContent])) {
            $content = self::clearContent($record[$cellNumContent]['base_expr']);
        }
        return [
            'title' => base64_encode($title),
            'content' => base64_encode($content)
        ];
    }

    /**
     * Getting insert data to db for posts
     *
     * @param array $sqlLists
     * @param string|null $sqlHeader
     *
     * @return array
     */
    private static function listInsertPosts(array $sqlLists, string &$sqlHeader = null): array
    {
        $listRows = [];

        $isInsert = false;
        $count = count($sqlLists);
        for ($i = 0; $i < $count; $i++) {
            $row = self::Trim($sqlLists[$i]);
            if ($isInsert) {
                if (mb_strpos($row, '(') !== 0 && $row !== '') {
                    break;
                }
                $listRows[] = $row;
            } elseif (mb_strpos($row, 'INSERT INTO ') === 0
                && mb_strpos($row, '_posts') !== false
                && mb_strpos($row, 'post_title') !== false
                && mb_strpos($row, 'post_content') !== false) {
                $isInsert = true;
                $sqlHeader = (string) $row;
                $start = mb_strpos($row, 'VALUES(');
                if ($start !== false) {
                    $sqlHeader = mb_substr($row, 0, $start + 6);
                    $row = mb_substr($row, $start + 6);
                    $listRows[] = $row;
                }
            }
        }

        return $listRows;
    }

    /**
     * Remove quotes from string (start and end)
     *
     * @param string $string
     *
     * @return  string
     */
    private static function removeQuotes(string $string) :string
    {
        if (mb_substr($string, 0, 1) === "'" || mb_substr($string, 0, 1) === '"') {
            $string = mb_substr($string, 1);
        }
        if (mb_substr($string, mb_strlen($string) - 1, 1) === "'" || mb_substr($string, mb_strlen($string) - 1, 1) === '"') {
            $string = mb_substr($string, 0, mb_strlen($string) - 1);
        }
        return $string;
    }

    /**
     * Remove tags from string (self::$listRemoveTags)
     *
     * @param string $string
     *
     * @return  string
     */
    private static function clearContent(string $string) :string
    {
        $content = stripslashes(self::removeQuotes($string));

        $start = 0;

        while ($start !== false) {
            $start = mb_strpos($content, '<', $start, 'utf-8');
            if ($start !== false) {
                $end = mb_strpos($content, '>', $start);
                if ($end !== false) {
                    $tag = self::Trim(mb_substr($content, $start + 1, $end - $start - 1));
                    $arr = explode(' ', str_replace("\t", ' ', $tag));
                    if (is_array($arr) && !empty($arr[0])) {
                        $nameTag = self::Trim(mb_strtolower($arr[0], 'utf-8'));
                        if (in_array($nameTag, self::$listRemoveTags, true)) {
                            $content = mb_substr($content, 0, $start) . mb_substr($content, $end + 1);
                        }
                    }
                }
                $start++;
            }
        }

        return $content;
    }

    /**
     * Erase empty space from start and end string
     *
     * @param string $string
     *
     * @return  string
     */
    private static function Trim(string $string) :string
    {
        return preg_replace( "/(^\s+)|(\s+$)/us", "", $string );
    }
}