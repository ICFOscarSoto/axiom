<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPOfferPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPOfferPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPOfferPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPOfferPrices[]    findAll()
 * @method ERPOfferPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPOfferPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPOfferPrices::class);
    }
    
  
    public function validOffer($id,$customer, $quantity, $start){
        if($quantity==NULL) $qty=1;
        else $qty=$quantity;
        $date=$start->format("Y-m-d");
        if($id==NULL)
        {
          if($customer!=NULL)
          {
            $query="SELECT * FROM erpoffer_prices o WHERE o.customer_id=:CUST AND o.quantity=:QTY AND (o.end>STR_TO_DATE(:date, '%Y-%m-%d') OR o.end IS NULL) AND o.start<STR_TO_DATE(:date, '%Y-%m-%d') AND o.active=1 AND o.deleted=0";
            $params=['CUST' => $customer->getId(),
                      'QTY' => $qty,
                     'date' => $date];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
          else{
            $query="SELECT * FROM erpoffer_prices o WHERE o.customer_id IS NULL AND o.quantity=:QTY AND (o.end>STR_TO_DATE(:date, '%Y-%m-%d') OR o.end IS NULL) AND o.start<STR_TO_DATE(:date, '%Y-%m-%d')  AND o.active=1 AND o.deleted=0";
            $params=['QTY' => $qty,
                     'date' => $date];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
        }
        else{
          if($customer!=NULL)
          {
            $query="SELECT * FROM erpoffer_prices o WHERE o.id!=:ID AND o.customer_id=:CUST AND o.quantity=:QTY AND (o.end>STR_TO_DATE(:date, '%Y-%m-%d') OR o.end IS NULL) AND o.start<STR_TO_DATE(:date, '%Y-%m-%d')  AND o.active=1 AND o.deleted=0";
            $params=['ID' => $id,
                      'CUST' => $customer->getId(),
                      'QTY' => $qty,
                     'date' => $date];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
          else{
            $query="SELECT * FROM erpoffer_prices o WHERE o.id!=:ID AND o.customer_id IS NULL AND o.quantity=:QTY AND (o.end>STR_TO_DATE(:date, '%Y-%m-%d') OR o.end IS NULL) AND o.start<STR_TO_DATE(:date, '%Y-%m-%d') AND o.active=1 AND o.deleted=0";
            $params=['ID' => $id,
                     'QTY' => $qty,
                     'date' => $date];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
        
        
        
        }
    }
    
    public function offerPricesByCustomer($customer){
      $query="SELECT c.code as Product, c.name as Name, p.increment as Increment, p.price as Price, p.start as Start, p.end as End
              FROM erpoffer_prices p 
              LEFT JOIN erpproducts c ON c.id=p.product_id 
              WHERE p.customer_id=:CUST AND p.active=TRUE and p.deleted=0";
      $params=['CUST' => $customer->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    
    }

    // /**
    //  * @return ERPOfferPrices[] Returns an array of ERPOfferPrices objects
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
    public function findOneBySomeField($value): ?ERPOfferPrices
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
