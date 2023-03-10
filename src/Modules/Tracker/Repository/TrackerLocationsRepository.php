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

    public function findPoints($tracker, $start, $end){
      return $this->createQueryBuilder('q')
          ->andWhere('q.tracker = :val_tracker')
          ->andWhere('q.date >= :val_start')
          ->andWhere('q.date <= :val_end')
          ->andWhere('q.active = 1')
          ->andWhere('q.deleted = 0')
          ->setParameter('val_tracker', $tracker)
          ->setParameter('val_start', $start)
          ->setParameter('val_end', $end)
          ->orderBy('q.date', 'ASC')
          ->getQuery()
          ->getResult()
      ;
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
