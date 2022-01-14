<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductsSuppliers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProductsSuppliers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProductsSuppliers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProductsSuppliers[]    findAll()
 * @method ERPProductsSuppliers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductsSuppliersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProductsSuppliers::class);
    }

    // /**
    //  * @return ERPProductsSuppliers[] Returns an array of ERPProductsSuppliers objects
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
    public function findOneBySomeField($value): ?ERPProductsSuppliers
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
