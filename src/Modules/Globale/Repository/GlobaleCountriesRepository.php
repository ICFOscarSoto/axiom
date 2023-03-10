<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleCountries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Countries|null find($id, $lockMode = null, $lockVersion = null)
 * @method Countries|null findOneBy(array $criteria, array $orderBy = null)
 * @method Countries[]    findAll()
 * @method Countries[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleCountriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleCountries::class);
    }

    // /**
    //  * @return Countries[] Returns an array of Countries objects
    //  */

    public function findById($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Countries
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
