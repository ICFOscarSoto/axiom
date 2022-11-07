<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPInventoryLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Modules\ERP\Entity\ERPSupplierCommentLines;

/**
 * @method ERPInventoryLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPInventoryLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPInventoryLocation[]    findAll()
 * @method ERPInventoryLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPInventoryLocationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPInventoryLocation::class);
    }

    // /**
    //  * @return ERPInventoryLocation[] Returns an array of ERPInventoryLocation objects
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
    public function findOneBySomeField($value): ?ERPInventoryLocation
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
