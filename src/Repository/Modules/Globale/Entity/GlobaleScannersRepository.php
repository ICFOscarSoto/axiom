<?php

namespace App\Repository\Modules\Globale\Entity;

use App\Modules\Globale\Entity\GlobaleScanners;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleScanners|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleScanners|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleScanners[]    findAll()
 * @method GlobaleScanners[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleScannersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleScanners::class);
    }

    // /**
    //  * @return GlobaleScanners[] Returns an array of GlobaleScanners objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobaleScanners
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
