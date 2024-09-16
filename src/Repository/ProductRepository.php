<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findWithFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('p');

        $valid_keys = ['calories', 'protein', 'fat', 'sodium', 'fiber', 'carbo', 'sugars', 'potass', 'vitamins', 'shelf', 'weight', 'cups'];
        foreach ($filters as $key => $value) {
            if($key == 'sort'){
                continue;
            }
            if(!in_array($key, $valid_keys)){
                throw new \InvalidArgumentException("Invalid filter key: $key");
            }
            $operator = preg_match('/^(\w+)([<>=!])$/', $key, $matches) ? $matches[2] : '=';
            $numeric_value = preg_match('/^\d+$/', $value) ? $value : null;	
            if(!in_array($operator, ['<', '>', '=', '!=', '<=', '>='])){
                throw new \InvalidArgumentException("Invalid operator for $key: $operator");
            }
            $qb->andWhere("p.$key $operator :$key")
                ->setParameter($key, $numeric_value);
        }

        if(isset($filters['sort'])){
            $sort = $filters['sort'];
            if(!in_array($sort, $valid_keys)){
                throw new \InvalidArgumentException("Invalid sort key: $sort");
            }
            $qb->orderBy("p.$sort");
        }

        return $qb->getQuery()->getResult();
    }

}
