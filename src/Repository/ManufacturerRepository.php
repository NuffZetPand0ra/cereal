<?php

namespace App\Repository;

use App\Entity\Manufacturer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Manufacturer>
 */
class ManufacturerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Manufacturer::class);
    }

    public function findOneByShorthand(string $shorthand): ?Manufacturer
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.shorthand = :shorthand')
            ->setParameter('shorthand', $shorthand)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
