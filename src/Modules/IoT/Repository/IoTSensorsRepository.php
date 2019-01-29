<?php

namespace App\Modules\IoT\Repository;

use App\Modules\IoT\Entity\IoTSensors;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IoTSensors|null find($id, $lockMode = null, $lockVersion = null)
 * @method IoTSensors|null findOneBy(array $criteria, array $orderBy = null)
 * @method IoTSensors[]    findAll()
 * @method IoTSensors[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IoTSensorsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IoTSensors::class);
    }

    // /**
    //  * @return IoTSensors[] Returns an array of IoTSensors objects
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
    public function findOneBySomeField($value): ?IoTSensors
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
