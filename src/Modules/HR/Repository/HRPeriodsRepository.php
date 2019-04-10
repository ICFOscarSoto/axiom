<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRPeriods;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRPeriods|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRPeriods|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRPeriods[]    findAll()
 * @method HRPeriods[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRPeriodsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRPeriods::class);
    }

    // /**
    //  * @return HRPeriods[] Returns an array of HRPeriods objects
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
    public function findOneBySomeField($value): ?HRPeriods
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
