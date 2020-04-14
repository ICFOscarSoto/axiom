<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSupplierOrdersData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSupplierOrdersData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSupplierOrdersData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSupplierOrdersData[]    findAll()
 * @method ERPSupplierOrdersData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSupplierOrdersDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSupplierOrdersData::class);
    }

    public function ordersdataBySupplier($supplier){

      $query="SELECT *
              FROM erpsupplier_orders_data s
              WHERE s.supplier_id=:SUP AND S.active=TRUE and S.deleted=0";
      $params=['SUP'  =>  $supplier->getId()];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      return $result;

    }

    // /**
    //  * @return ERPSupplierOrdersData[] Returns an array of ERPSupplierOrdersData objects
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
    public function findOneBySomeField($value): ?ERPSupplierOrdersData
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
