<?php

namespace App\Modules\IoT\Repository;

use App\Modules\IoT\Entity\IoTData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IoTData|null find($id, $lockMode = null, $lockVersion = null)
 * @method IoTData|null findOneBy(array $criteria, array $orderBy = null)
 * @method IoTData[]    findAll()
 * @method IoTData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IoTDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IoTData::class);
    }

    // /**
    //  * @return IoTData[] Returns an array of IoTData objects
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
    public function findOneBySomeField($value): ?IoTData
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
