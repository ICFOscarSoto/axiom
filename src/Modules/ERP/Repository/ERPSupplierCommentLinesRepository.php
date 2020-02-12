<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSupplierCommentLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSupplierCommentLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSupplierCommentLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSupplierCommentLines[]    findAll()
 * @method ERPSupplierCommentLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSupplierCommentLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSupplierCommentLines::class);
    }

    // /**
    //  * @return ERPSupplierCommentLines[] Returns an array of ERPSupplierCommentLines objects
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
    public function findOneBySomeField($value): ?ERPSupplierCommentLines
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
