<?php

namespace App\Repository;

use App\Entity\Product;
use App\Service\ProductFilterService;
use App\Model\Filter;
use BadMethodCallException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use RuntimeException;
use LogicException;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * 
     * @param Filter[] $filters 
     * @return array 
     */
    public function findWithFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('p');

        $valid_keys = ['name', 'calories', 'protein', 'fat', 'sodium', 'fiber', 'carbo', 'sugars', 'potass', 'vitamins', 'shelf', 'weight', 'cups'];

        $keys_used = [];
        foreach($valid_keys as $key){
            $keys_used[$key] = 0;
        }

        // echo "<pre>";var_dump($filters);echo "</pre>";exit;
        foreach ($filters as $filter) {

            if($filter->key == 'order'){
                $order = $filter->operator == '>' ? 'DESC' : 'ASC';
                $qb->orderBy("p.{$filter->value}", $order);
                continue;
            }

            if (!in_array($filter->key, $valid_keys)) {
                throw new InvalidArgumentException("Invalid filter key: {$filter->key}");
            }

            // Make sure the key is unique
            $key_name = $filter->key."__".++$keys_used[$filter->key]."__".uniqid();


            if ($filter->key === 'name' && $filter->operator === "=" && strpos($filter->value, '%') !== false) {
                $qb->andWhere("LOWER(p.{$filter->key}) LIKE LOWER(:{$key_name})")
                    ->setParameter($key_name, $filter->value);
            } else {
                $qb->andWhere("p.{$filter->key} {$filter->operator} :{$key_name}")
                    ->setParameter($key_name, $filter->value);
            }

        }
        // echo "<pre>";var_dump($qb->getQuery()->getSql(), $qb->getQuery()->getParameters());echo "</pre>";exit;
        return $qb->getQuery()->getResult();
    }

}
