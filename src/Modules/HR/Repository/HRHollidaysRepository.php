<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRHollidays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRHollidays|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRHollidays|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRHollidays[]    findAll()
 * @method HRHollidays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRHollidaysRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRHollidays::class);
    }

    // /**
    //  * @return HRHollidays[] Returns an array of HRHollidays objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HRHollidays
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
