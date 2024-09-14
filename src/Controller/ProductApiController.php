<?php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

use App\Model\ProductDto;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class ProductApiController extends AbstractApiController
{
    public function show(int $id, EntityManagerInterface $em) : Response
    {
        try{
            $product = $em->getRepository(Product::class)->find($id);
            if(!$product){
                throw new \Exception("Product not found");
            }
            $response = new Response(
                content: $this->jsonSerialize($product),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            $response = new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 404
            );
        }
        return $response;
    }

    public function update(#[MapRequestPayload(
        acceptFormat: ['json'],
    )] Product $product, EntityManagerInterface $em) : Response
    {
        $em->persist($product);
        try{
            $em->flush();
            $response = new Response(
                content: $this->jsonSerialize($product),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            $response = new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
        return $response;
    }

    public function create(#[MapRequestPayload(
        acceptFormat: ['json'],
    )] Product $product, EntityManagerInterface $em) : Response
    {
        $em->persist($product);
        try{
            $em->flush();
            $response = new Response(
                content: $this->jsonSerialize($product),
                headers: ['Content-Type' => 'application/json'],
                status: 201
            );
        } catch (\Exception $e) {
            $response = new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
        return $response;
    }

    public function delete(int $id, EntityManagerInterface $em) : Response
    {
        try{
            $product = $em->getRepository(Product::class)->find($id);
            $em->remove($product);
            $em->flush();
            $response = new Response(
                content: json_encode(['message' => 'Product deleted']),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            $response = new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
        return $response;
    }

    public function list(Request $request, EntityManagerInterface $em) : Response
    {
        $filters = $request->query->all();
        try{
            $products = $em->getRepository(Product::class)->findWithFilters($filters);
            $response = new Response(
                content: json_encode($products),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            $response = new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
        return $response;
    }
}

# /products?weight={>=100}