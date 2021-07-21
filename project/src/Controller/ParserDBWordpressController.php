<?php

namespace App\Controller;

use App\Service\DbHandler;
use App\Service\SqlParser;
use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParserDBWordpressController extends AbstractController
{
    /**
     * @Route("/parse/{filename}", name="parser_db_wordpress")
     */
    public function index(DbHandler $dbHandler, string $filename, Storage $storage): Response
    {
        $webPath = $this->getParameter('kernel.project_dir') . '/public/uploads/db/';

        $path = str_replace('../', '',"$webPath$filename");

        try {
            $list = $dbHandler->listPosts($path);
        } catch (\Exception $e) {
            return $this->forward('App\Controller\ListDBWordpressController::index', [
                'storage'  => $storage,
                'error' => $e->getMessage(),
            ]);
        }

        $response = $this->render('parser_db_wordpress/index.html.twig', [
            'file' => $path,
            'list' => $list,
        ]);
        
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
