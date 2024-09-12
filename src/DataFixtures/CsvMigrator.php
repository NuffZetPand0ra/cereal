<?php

namespace App\DataFixtures;

use Doctrine\Migrations\AbstractMigration;

require_once __DIR__.'/CsvParser.php';

abstract class CsvMigrator extends AbstractMigration
{
    protected function MigrateDataFromCsv()
    {
        $csv_parser = new CsvParser(__DIR__.'/cereal.csv');
        $manufacturers = $csv_parser->getAllManufacturers();
        $product_types = $csv_parser->getAllProductTypes();
        foreach($manufacturers as $manufacturer){
            $this->addSql('INSERT INTO manufacturer (name, shorthand) VALUES (?, ?)', [$manufacturer->getName(), $manufacturer->getShorthand()]);
        }
        foreach($product_types as $product_type){
            $this->addSql('INSERT INTO product_type (name, shorthand) VALUES (?, ?)', [$product_type->getName(), $product_type->getShorthand()]);
        }
        $products = $csv_parser->getAllProducts();
        foreach($products as $product){
            $this->addSql('INSERT INTO product 
                (mfr, type, name, calories, protein, fat, sodium, fiber, carbo, sugars, potass, vitamins, shelf, weight, cups)
                VALUES (
                    (SELECT id FROM manufacturer WHERE shorthand = :mfr), 
                    (SELECT id FROM product_type WHERE shorthand = :type), 
                    :name, 
                    :calories, 
                    :protein, 
                    :fat, 
                    :sodium, 
                    :fiber, 
                    :carbo, 
                    :sugars, 
                    :potass, 
                    :vitamins, 
                    :shelf, 
                    :weight, 
                    :cups)', 
                [
                    'mfr' => $product->getMfr()->getShorthand(),
                    'type' => $product->getType()->getShorthand(),
                    'name' => $product->getName(),
                    'calories' => $product->getCalories(),
                    'protein' => $product->getProtein(),
                    'fat' => $product->getFat(),
                    'sodium' => $product->getSodium(),
                    'fiber' => $product->getFiber(),
                    'carbo' => $product->getCarbo(),
                    'sugars' => $product->getSugars(),
                    'potass' => $product->getPotass(),
                    'vitamins' => $product->getVitamins(),
                    'shelf' => $product->getShelf(),
                    'weight' => $product->getWeight(),
                    'cups' => $product->getCups()
                ]
            );
        }
    }
}