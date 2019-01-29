<?php

namespace App\Modules\IoT\Repository;

use App\Modules\IoT\Entity\IoTDevices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IoTDevices|null find($id, $lockMode = null, $lockVersion = null)
 * @method IoTDevices|null findOneBy(array $criteria, array $orderBy = null)
 * @method IoTDevices[]    findAll()
 * @method IoTDevices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IoTDeviceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IoTDevices::class);
    }

    // /**
    //  * @return IoTDevices[] Returns an array of IoTDevices objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IoTDevices
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
