<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleActivities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Activities|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activities|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activities[]    findAll()
 * @method Activities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleActivities::class);
    }

    // /**
    //  * @return Activities[] Returns an array of Activities objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Activities
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
