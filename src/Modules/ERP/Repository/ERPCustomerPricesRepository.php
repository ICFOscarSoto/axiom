<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomerPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomerPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomerPrices[]    findAll()
 * @method ERPCustomerPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomerPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomerPrices::class);
    }

    // /**
    //  * @return ERPCustomerPrices[] Returns an array of ERPCustomerPrices objects
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
    public function findOneBySomeField($value): ?ERPCustomerPrices
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function existPrice($product,$customer,$supplier){
      $query="SELECT * FROM erpcustomer_prices p WHERE p.product_id=:PROD AND p.customer_id=:CST AND p.supplier_id=:SUP AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId(),
               'CST' => $customer->getId(),
               'SUP' => $supplier->getId(),
        ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
    //  dump($result);
      if($result!=NULL) return true;
      else return false;
    }

    public function pricesByProductSupplier($product,$supplier){

      $query="SELECT c.name as Customer, p.increment as Increment, p.price as Price
              FROM erpcustomer_prices p
              LEFT JOIN erpcustomers c ON c.id=p.customer_id
              WHERE p.product_id=:PROD AND p.supplier_id=:SUP AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId(),
               'SUP'  =>  $supplier->getId()
              ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;

    }

    public function findCustomersByProduct($product){

      $query="SELECT p.customer_id as customer
              FROM erpcustomer_prices p
              WHERE p.product_id=:PROD AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId()
              ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;

    }
}
