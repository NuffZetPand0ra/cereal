<?php

namespace App\Controller;

use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\ProductType;
use App\Service\ProductFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    public function index(Request $r, EntityManagerInterface $em, ProductFilterService $productFilterService): Response
    {
        $filters = $productFilterService->parseFilters($r);
        $products = $em->getRepository(Product::class)->findWithFilters($filters);
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
            'filters' => $filters,
        ]);
    }
    
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        if(!$product) {
            throw $this->createNotFoundException('Product with id '.$id.' not found');
        }
        $image = $em->getRepository(ProductImage::class)->findOneBy(['product' => $product]);
        $image_base64 = base64_encode(stream_get_contents($image->getImageData()));
        // var_dump(base64_encode(stream_get_contents($image->getImageData())));exit;
        return $this->render('product/single.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'id' => $id,
            'image' => $image,
            'image_base64' => $image_base64,
        ]);
    }

    public function edit(int $id, EntityManagerInterface $em): Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        if(!$product) {
            throw $this->createNotFoundException('Product with id '.$id.' not found');
        }
        $manufacturers = $em->getRepository(Manufacturer::class)->findAll();
        $types = $em->getRepository(ProductType::class)->findAll();
        return $this->render('product/edit.html.twig', [
            'controller_name' => 'ProductController',
            'product' => $product,
            'mfrs' => $manufacturers,
            'types' => $types,
        ]);
    }

    public function create(EntityManagerInterface $em): Response
    {
        $manufacturers = $em->getRepository(Manufacturer::class)->findAll();
        $types = $em->getRepository(ProductType::class)->findAll();
        return $this->render('product/edit.html.twig', [
            'controller_name' => 'ProductController',
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
        try{
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', 'Product saved');
            return $this->redirectToRoute('single_product_show', ['id' => $product->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error saving product: '.$e->getMessage());
            if($product->getId()) {
                return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
            } else {
                return $this->redirectToRoute('product_index');
            }
        }
    }

    public function delete(int $id, EntityManagerInterface $em) : Response
    {
        $product = $em->getRepository(Product::class)->find($id);
        $product_image = $em->getRepository(ProductImage::class)->findOneBy(['product' => $product]);
        if(!$product) {
            $this->addFlash('error', 'Product with id '.$id.' not found');
            return $this->redirectToRoute('product_index');
        }try{
            $em->remove($product);
            if($product_image) {
                $em->remove($product_image);
            }
            $em->flush();
            $this->addFlash('success', "Deleted product ".$product->getName());
            return $this->redirectToRoute('product_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting product '.$product->getName().': '.$e->getMessage());
            return $this->redirectToRoute('product_index');
        }
    }
}
