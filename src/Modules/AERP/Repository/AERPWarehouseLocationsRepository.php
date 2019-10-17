<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPWarehouseLocations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPWarehouseLocations|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPWarehouseLocations|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPWarehouseLocations[]    findAll()
 * @method AERPWarehouseLocations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPWarehouseLocationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPWarehouseLocations::class);
    }

    // /**
    //  * @return AERPWarehouseLocations[] Returns an array of AERPWarehouseLocations objects
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
    public function findOneBySomeField($value): ?AERPWarehouseLocations
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
