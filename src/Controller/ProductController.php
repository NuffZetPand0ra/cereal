<?php

namespace App\Controller;

use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\ProductType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        // $product = (new Product())
        //     ->setName('My cereal product')
        //     ->setMfr((new Manufacturer())->setName('My manufacturer')->setShorthand('M'))
        //     ->setType((new ProductType())->setName('My product type')->setShorthand('T'))
        //     ->setCalories(100);
        return $this->render('product/single.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'id' => $id,
        ]);
    }

    public function index(): Response
    {
        exit("Product list comes here");
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
    public function edit(int $id, EntityManagerInterface $em): Response
    {
        // $product = (new Product())
        //     ->setName('My cereal product')
        //     ->setMfr((new Manufacturer())->setName('My manufacturer')->setShorthand('M'))
        //     ->setType((new ProductType())->setName('My product type')->setShorthand('T'))
        //     ->setCalories(100);
        // $em->persist($product);
        // $em->flush();
        $product = $em->getRepository(Product::class)->find($id);
        return $this->render('product/edit.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
        ]);
    }

    public function save(Request $r, EntityManagerInterface $em) : Response
    {
        $product = (new Product())
            ->setName($r->get('name'))
            ->setMfr((new Manufacturer())->setName($r->get('mfr_name'))->setShorthand($r->get('mfr_shorthand')))
            ->setType((new ProductType())->setName($r->get('type_name'))->setShorthand($r->get('type_shorthand')))
            ->setCalories($r->get('calories'));
        $em->persist($product);
        $em->flush();
        return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
    }
}
