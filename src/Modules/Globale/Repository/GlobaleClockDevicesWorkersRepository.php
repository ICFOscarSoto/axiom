<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleClockDevicesWorkers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleClockDevicesWorkers|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleClockDevicesWorkers|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleClockDevicesWorkers[]    findAll()
 * @method GlobaleClockDevicesWorkers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleClockDevicesWorkersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleClockDevicesWorkers::class);
    }

    // /**
    //  * @return GlobaleClockDevicesWorkers[] Returns an array of GlobaleClockDevicesWorkers objects
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
    public function findOneBySomeField($value): ?GlobaleClockDevicesWorkers
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
