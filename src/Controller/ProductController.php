<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function show(): Response
    {
        return $this->render('product/single.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
}
