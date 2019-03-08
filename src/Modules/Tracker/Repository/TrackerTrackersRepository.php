<?php

namespace App\Modules\Tracker\Repository;

use App\Modules\Tracker\Entity\TrackerTrackers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TrackerTrackers|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackerTrackers|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackerTrackers[]    findAll()
 * @method TrackerTrackers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackerTrackersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TrackerTrackers::class);
    }

    // /**
    //  * @return TrackerTrackers[] Returns an array of TrackerTrackers objects
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
    public function findOneBySomeField($value): ?TrackerTrackers
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
