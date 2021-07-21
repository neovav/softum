<?php

namespace App\Controller;

use App\Service\DbHandler;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnionParserDBWordpressController extends AbstractController
{
    /**
     * @Route("/union/", name="union_parser_d_b_wordpress")
     */
    public function index(DbHandler $dbHandler, Storage $storage): Response
    {
        $list = [];

        $webPath = $this->getParameter('kernel.project_dir') . '/public/uploads/db/';

        if (empty($_POST)) {
            throw new \Exception('File names is absent');
        }

        $listFileNames = [];
        $listFileIsAbsent = [];

        foreach ($_POST as $fileName) {
            $path = str_replace('../', '',"$webPath$fileName");
            if (!file_exists($path)) {
                $listFileIsAbsent[] = $fileName;
            } else {
                $listFileNames[] = $path;
            }
        }

        if (!empty($listFileIsAbsent)) {
            return $this->forward('App\Controller\ListDBWordpressController::index', [
                'storage'  => $storage,
                'error' => "Отсутствуют переданные файлы: \n" . implode(",\n", $listFileIsAbsent),
            ]);
        }

        try {
            foreach ($listFileNames as $path) {
                $data = $dbHandler->listPosts($path);
                if (is_array($data) && !empty($data)) {
                    $list = array_merge($list, $data);
                }
            }
        } catch (\Exception $e) {
            return $this->forward('App\Controller\ListDBWordpressController::index', [
                'storage'  => $storage,
                'error' => $e->getMessage(),
            ]);
        }

        $response = $this->render('parser_db_wordpress/index.html.twig', [
            'list' => $list,
        ]);

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
