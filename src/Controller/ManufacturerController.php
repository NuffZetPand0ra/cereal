<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ManufacturerController extends AbstractController
{
    #[Route('/manufacturer', name: 'app_manufacturer')]
    public function index(): Response
    {
        return $this->render('manufacturer/index.html.twig', [
            'controller_name' => 'ManufacturerController',
        ]);
    }
}
