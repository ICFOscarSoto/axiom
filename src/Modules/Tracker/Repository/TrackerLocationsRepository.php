<?php

namespace App\Modules\Tracker\Repository;

use App\Modules\Tracker\Entity\TrackerLocations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TrackerLocations|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackerLocations|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackerLocations[]    findAll()
 * @method TrackerLocations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackerLocationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TrackerLocations::class);
    }

    // /**
    //  * @return TrackerLocations[] Returns an array of TrackerLocations objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TrackerLocations
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
