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
        return $this->render('product/single.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'id' => $id,
        ]);
    }

    public function index(EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findAll();
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
        ]);
    }
    public function edit(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        $manufacturers = $em->getRepository(Manufacturer::class)->findAll();
        $types = $em->getRepository(ProductType::class)->findAll();
        return $this->render('product/edit.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'mfrs' => $manufacturers,
            'types' => $types,
        ]);
    }

    public function save(Request $r, EntityManagerInterface $em) : Response
    {
        if($r->get('id')) {
            $product = $em->getRepository(Product::class)->find($r->get('id'));
        } else {
            $product = new Product();
        }
        $product
            ->setName($r->get('name'))
            ->setMfr($em->getRepository(Manufacturer::class)->find($r->get('mfr')))
            ->setType($em->getRepository(ProductType::class)->find($r->get('type')))
            ->setCalories($r->get('calories'))
            ->setProtein($r->get('protein'))
            ->setFat($r->get('fat'))
            ->setSodium($r->get('sodium'))
            ->setFiber($r->get('fiber'))
            ->setCarbo($r->get('carbo'))
            ->setSugars($r->get('sugars'))
            ->setPotass($r->get('potass'))
            ->setVitamins($r->get('vitamins'))
            ->setShelf($r->get('shelf'))
            ->setWeight($r->get('weight'))
            ->setCups($r->get('cups'));

        $em->persist($product);
        $em->flush();
        $this->addFlash('success', 'Product saved');
        return $this->redirectToRoute('single_product_show', ['id' => $product->getId()]);
    }
}
