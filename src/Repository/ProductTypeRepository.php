<?php

namespace App\Repository;

use App\Entity\ProductType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductType>
 */
class ProductTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductType::class);
    }

    public function findOneByShorthand(string $shorthand): ?ProductType
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.shorthand = :shorthand')
            ->setParameter('shorthand', $shorthand)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
