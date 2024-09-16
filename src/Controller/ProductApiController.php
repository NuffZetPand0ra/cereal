<?php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductApiController extends AbstractApiController
{
    public function show(int $id, EntityManagerInterface $em) : Response
    {
        try{
            $product = $em->getRepository(Product::class)->find($id);
            if(!$product){
                throw new NotFoundHttpException("Product not found");
            }
            return new Response(
                content: $this->jsonSerialize($product),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            return new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 404
            );
        }
    }

    public function update(#[MapRequestPayload(
        acceptFormat: ['json'],
    )] Product $product, EntityManagerInterface $em) : Response
    {
        $em->persist($product);
        try{
            $em->flush();
            return new Response(
                content: $this->jsonSerialize($product),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            return new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
    }

    public function create(#[MapRequestPayload(
        acceptFormat: ['json'],
    )] Product $product, EntityManagerInterface $em) : Response
    {
        $em->persist($product);
        try{
            $em->flush();
            return new Response(
                content: $this->jsonSerialize($product),
                headers: ['Content-Type' => 'application/json'],
                status: 201
            );
        } catch (\Exception $e) {
            return new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
    }

    public function delete(int $id, EntityManagerInterface $em) : Response
    {
        try{
            $product = $em->getRepository(Product::class)->find($id);
            $em->remove($product);
            $em->flush();
            return new Response(
                content: json_encode(['message' => 'Product deleted']),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            return new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
    }

    public function list(Request $request, EntityManagerInterface $em) : Response
    {
        $filters = $request->query->all();
        try{
            $products = $em->getRepository(Product::class)->findWithFilters($filters);
            return new Response(
                content: $this->jsonSerialize($products),
                headers: ['Content-Type' => 'application/json'],
                status: 200
            );
        } catch (\Exception $e) {
            return new Response(
                content: json_encode(['error' => $e->getMessage()]),
                headers: ['Content-Type' => 'application/json'],
                status: 400
            );
        }
    }
}

# /products?weight={>=100}