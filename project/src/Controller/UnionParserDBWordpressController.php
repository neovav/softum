<?php

namespace App\Controller;

use App\Service\DbHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnionParserDBWordpressController extends AbstractController
{
    /**
     * @Route("/union/", name="union_parser_d_b_wordpress")
     */
    public function index(Request $request, DbHandler $dbHandler): Response
    {
        $list = [];

        $webPath = $this->getParameter('kernel.project_dir') . '/public/uploads/db/';

        if (!empty($_POST)) {
            foreach ($_POST as $fileName) {
                $path = str_replace('../', '',"$webPath$fileName");
                if (file_exists($path)) {
                    $data = $dbHandler->listPosts($path);
                    if (is_array($data) && !empty($data)) {
                        $list = array_merge($list, $data);
                    }
                }
            }
        }

        $response = $this->render('parser_db_wordpress/index.html.twig', [
            'list' => $list,
        ]);

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
