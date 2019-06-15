<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleClockDevices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleClockDevices|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleClockDevices|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleClockDevices[]    findAll()
 * @method GlobaleClockDevices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleClockDevicesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleClockDevices::class);
    }

    // /**
    //  * @return GlobaleClockDevices[] Returns an array of GlobaleClockDevices objects
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
    public function findOneBySomeField($value): ?GlobaleClockDevices
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
