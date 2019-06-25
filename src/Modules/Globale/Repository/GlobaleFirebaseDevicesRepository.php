<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleFirebaseDevices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleFirebaseDevices|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleFirebaseDevices|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleFirebaseDevices[]    findAll()
 * @method GlobaleFirebaseDevices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleFirebaseDevicesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleFirebaseDevices::class);
    }

    // /**
    //  * @return GlobaleFirebaseDevices[] Returns an array of GlobaleFirebaseDevices objects
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
    public function findOneBySomeField($value): ?GlobaleFirebaseDevices
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
