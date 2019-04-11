<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoreLocations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoreLocations|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoreLocations|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoreLocations[]    findAll()
 * @method ERPStoreLocations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoreLocationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoreLocations::class);
    }

    // /**
    //  * @return ERPStoreLocations[] Returns an array of ERPStoreLocations objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ERPStoreLocations
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
