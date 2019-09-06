<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleDiskUsages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleDiskUsages|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleDiskUsages|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleDiskUsages[]    findAll()
 * @method GlobaleDiskUsages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleDiskUsagesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleDiskUsages::class);
    }

    // /**
    //  * @return GlobaleDiskUsages[] Returns an array of GlobaleDiskUsages objects
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
    public function findOneBySomeField($value): ?GlobaleDiskUsages
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
