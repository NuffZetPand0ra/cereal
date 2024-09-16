<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\{Manufacturer, Product, ProductType, ProductImage};

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $csv_parser = new CsvParser(__DIR__.'/cereal.csv');
        $manufacturers = $csv_parser->getAllManufacturers();
        $product_types = $csv_parser->getAllProductTypes();
        foreach($manufacturers as $manufacturer){
            $manager->persist($manufacturer);
        }
        foreach($product_types as $product_type){
            $manager->persist($product_type);
        }

        $manager->flush();

        $products = $csv_parser->getAllProducts();
        foreach($products as $product){
            $product
                ->setMfr($manager->getRepository(Manufacturer::class)->findOneByShorthand($product->getMfr()->getShorthand()))
                ->setType($manager->getRepository(ProductType::class)->findOneByShorthand($product->getType()->getShorthand()));
            $manager->persist($product);
        }
        $manager->flush();

        $products = $manager->getRepository(Product::class)->findAll();
        foreach($products as $product){
            $image_path = null;
            $files_endings = ['jpg', 'png'];
            foreach($files_endings as $file_ending){
                $image_path_to_try = __DIR__.'/images/'.$product->getName().'.'.$file_ending;
                if(file_exists($image_path_to_try)){
                    $image_path = $image_path_to_try;
                    break;
                }
            }
            if(is_null($image_path)){
                echo "Image not found for product: ".$product->getName()."\n";
                continue;
            }

            $image_size = getimagesize($image_path);
            $product_image = new ProductImage();
            $product_image
                ->setProduct($product)
                ->setWidth($image_size[0])
                ->setHeight($image_size[1])
                ->setMimeType($image_size['mime'])
                ->setImageData(file_get_contents($image_path));
            $manager->persist($product_image);
            
        }
        $manager->flush();
    }
}
