<?php
namespace App\DataFixtures;

use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\ProductType;
use InvalidArgumentException;

class CsvParser
{
    private array $data = [];

    public function __construct(
        private string $filename,
        private string $delimiter = ';',
        private int $skipRows = 2
    ) {
        $csv_data = str_getcsv(file_get_contents($filename), "\n");
        $csv_data = array_slice($csv_data, $skipRows);
        foreach($csv_data as $row){
            $this->data[] = str_getcsv($row, $delimiter);
        }
    }

    private function getManufacturerData(string $shorthand): Manufacturer
    {
        $manufacturer_map = [
            "A" => 'American Home Food Products',
            "K" => 'Kelloggs',
            "G" => 'General Mills',
            "N" => 'Nabisco',
            "P" => 'Post',
            "Q" => 'Quaker Oats',
            "R" => 'Ralston Purina'
        ];
        if(!isset($manufacturer_map[$shorthand])){
            throw new \InvalidArgumentException("Unregistered manufacturer shorthand: $shorthand");
        }
        return (new Manufacturer())
            ->setShorthand($shorthand)
            ->setName($manufacturer_map[$shorthand]);
    }

    private function getProductTypeData(string $shorthand): ProductType
    {
        $product_type_map = [
            "C" => 'Cold',
            "H" => 'Hot'
        ];
        if(!isset($product_type_map[$shorthand])){
            throw new \InvalidArgumentException("Unregistered product type shorthand: $shorthand");
        }
        return (new ProductType())
            ->setShorthand($shorthand)
            ->setName($product_type_map[$shorthand]);
    }

    /**
     * 
     * @return Manufacturer[]
     * @throws InvalidArgumentException 
     */
    public function getAllManufacturers(): array
    {
        $manufacturers = [];
        foreach($this->data as $row){
            $manufacturers[$row[1]] = $this->getManufacturerData($row[1]);
        }
        return array_values($manufacturers);
    }

    /**
     * 
     * @return ProductType[]
     * @throws InvalidArgumentException 
     */
    public function getAllProductTypes(): array
    {
        $product_types = [];
        foreach($this->data as $row){
            $product_types[$row[2]] = $this->getProductTypeData($row[2]);
        }
        return $product_types;
    }

    /**
     * 
     * @return Product[]
     * @throws InvalidArgumentException 
     */
    public function getAllProducts(): array
    {
        $products = [];
        foreach($this->data as $row){
            $product = new Product();
            $product
                ->setName($row[0])
                ->setCalories($row[3])
                ->setProtein($row[4])
                ->setFat($row[5])
                ->setSodium($row[6])
                ->setFiber($row[7])
                ->setCarbo($row[8])
                ->setSugars($row[9])
                ->setPotass($row[10])
                ->setVitamins($row[11])
                ->setShelf($row[12])
                ->setWeight($row[13])
                ->setCups($row[14])
                ->setMfr($this->getManufacturerData($row[1]))
                ->setType($this->getProductTypeData($row[2]));
            $products[] = $product;
        }
        return $products;
    }
}
