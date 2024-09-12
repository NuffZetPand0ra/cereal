<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\{Manufacturer, Product, ProductType};

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
                ->setMfr($manager->getRepository(Manufacturer::class)->findOneBy(['shorthand' => $product->getMfr()->getShorthand()]))
                ->setType($manager->getRepository(ProductType::class)->findOneBy(['shorthand' => $product->getType()->getShorthand()]));
            $manager->persist($product);
        }
        $manager->flush();
    }
}
