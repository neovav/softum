<?php

namespace App\Controller;

use App\Service\Storage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListDBWordpressController extends AbstractController
{
    /**
     * @Route("/", name="list_db_wordpress")
     * @Route("/parse", name="parse_list_db_wordpress")
     */
    public function index(Storage $storage, string $error = null): Response
    {
        $webPath = $this->getParameter('kernel.project_dir') . '/public/uploads/db/';

        $files = $storage->list($webPath);

        return $this->render('list_db_wordpress/index.html.twig', [
            'listFiles' => ($files->count() > 0) ? $files : null,
            'error' => $error,
        ]);
    }
}
