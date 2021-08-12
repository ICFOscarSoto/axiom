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


    public function validOffer($id, $product, $customer, $quantity, $start, $end){
      if($quantity==NULL) $qty=1;
      else $qty=$quantity;
      $date_start=$start->format("Y-m-d");
      if($end!=NULL){
        $date_end=$end->format("Y-m-d");
        if($id==NULL){
          if($customer!=NULL){
            $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.customer_id=:CUST AND o.quantity=:QTY AND
            ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(:DATE_END) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END) IS NULL)
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END)<DATE(:DATE_END)))
            AND o.active=1 AND o.deleted=0";
            $params=['PROD' => $product->getId(),
            'CUST' => $customer->getId(),
            'QTY' => $qty,
            'DATE_START' => $date_start,
            'DATE_END' => $date_end];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
          else{
            $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.customer_id IS NULL AND o.quantity=:QTY AND
            ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(:DATE_END) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END) IS NULL)
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END)<DATE(:DATE_END)))
            AND o.active=1 AND o.deleted=0";
            $params=[
            'PROD' => $product->getId(),
            'QTY' => $qty,
            'DATE_START' => $date_start,
            'DATE_END' => $date_end];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
        }
        else{
          if($customer!=NULL){
            $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.id!=:ID AND o.customer_id=:CUST AND o.quantity=:QTY AND
            ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(:DATE_END) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END) IS NULL)
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END)<DATE(:DATE_END)))
            AND o.active=1 AND o.deleted=0";
            $params=[
            'PROD' => $product->getId(),
            'ID' => $id,
            'CUST' => $customer->getId(),
            'QTY' => $qty,
            'DATE_START' => $date_start,
            'DATE_END' => $date_end];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
          else{
            $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.id!=:ID AND o.customer_id IS NULL AND o.quantity=:QTY AND
            ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(:DATE_END) BETWEEN DATE(START) AND DATE(END))
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END) IS NULL)
            OR (DATE(START)<DATE(:DATE_END) AND DATE(END)<DATE(:DATE_END)))
            AND o.active=1 AND o.deleted=0";
            $params=[
            'PROD' => $product->getId(),
            'ID' => $id,
            'QTY' => $qty,
            'DATE_START' => $date_start,
            'DATE_END' => $date_end];
            $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
            if($result!=NULL) return true;
            else return false;
          }
        }
      }

      else{
        if($id==NULL){
            if($customer!=NULL){
                  $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.customer_id=:CUST AND o.quantity=:QTY AND
                  ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END) OR DATE(START)<DATE(:DATE_START) AND DATE(END) IS NULL)
                    OR (DATE(START)>DATE(:DATE_START)))
                  AND o.active=1 AND o.deleted=0";
                  $params=[
                  'PROD' => $product->getId(),
                  'CUST' => $customer->getId(),
                  'QTY' => $qty,
                  'DATE_START' => $date_start
                ];
                $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
                if($result!=NULL) return true;
                else return false;
            }
            else{
                $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.customer_id IS NULL AND o.quantity=:QTY AND
                ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END) OR DATE(START)<DATE(:DATE_START) AND DATE(END) IS NULL)
                  OR (DATE(START)>DATE(:DATE_START)))
                AND o.active=1 AND o.deleted=0";
                $params=[
                'PROD' => $product->getId(),
                'QTY' => $qty,
                'DATE_START' => $date_start
              ];
              $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
              if($result!=NULL) return true;
              else return false;
          }
      }
      else{
            if($customer!=NULL){
                $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.id!=:ID AND o.customer_id=:CUST AND o.quantity=:QTY AND
                ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END) OR DATE(START)<DATE(:DATE_START) AND DATE(END) IS NULL)
                  OR (DATE(START)>DATE(:DATE_START)))
                AND o.active=1 AND o.deleted=0";
                $params=[
                'PROD' => $product->getId(),
                'ID' => $id,
                'CUST' => $customer->getId(),
                'QTY' => $qty,
                'DATE_START' => $date_start
              ];
              $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
              if($result!=NULL) return true;
              else return false;
          }
          else{
            $query="SELECT * FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.id!=:ID AND o.customer_id IS NULL AND o.quantity=:QTY AND
              ((DATE(:DATE_START) BETWEEN DATE(START) AND DATE(END) OR DATE(START)<DATE(:DATE_START) AND DATE(END) IS NULL)
                OR (DATE(START)>DATE(:DATE_START)))
              AND o.active=1 AND o.deleted=0";
              $params=[
              'PROD' => $product->getId(),
              'ID' => $id,
              'QTY' => $qty,
              'DATE_START' => $date_start
              ];
              $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
              if($result!=NULL) return true;
              else return false;
          }
        }
      }
    }


    public function getAvailableOfferID($product,$today){
      $date_today=$today->format("Y-m-d");
      $query="SELECT  id as id FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.customer_id IS NULL AND o.quantity=1 AND
      ((DATE(START)<=DATE(:TODAY)) AND (DATE(END) IS NULL OR DATE(END)>=DATE(:TODAY)))
      AND o.active=1 AND o.deleted=0";
      $params=[
      'PROD' => $product->getId(),
      'TODAY' => $date_today
      ];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();

    }
    public function offerPricesByCustomer($customer){
      $query="SELECT c.code as Product, c.name as Name, p.increment as Increment, p.price as Price, p.start as Start, p.end as End
              FROM erpoffer_prices p
              LEFT JOIN erpproducts c ON c.id=p.product_id
              WHERE p.customer_id=:CUST AND p.active=TRUE and p.deleted=0";
      $params=['CUST' => $customer->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();


    }

    public function getAvailableQuantityOffers($product,$today){
      $date_today=$today->format("Y-m-d");
      $query="SELECT id as id FROM erpoffer_prices o WHERE o.product_id=:PROD AND o.customer_id IS NULL AND o.quantity>1 AND
      ((DATE(START)<=DATE(:TODAY)) AND (DATE(END) IS NULL OR DATE(END)>=DATE(:TODAY)))
      AND o.active=1 AND o.deleted=0";
      $params=[
      'PROD' => $product->getId(),
      'TODAY' => $date_today
      ];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }


    public function getOfferId($product,$customer,$qty,$price)
    {
      if($customer!=NULL){
        $query="SELECT o.id as id
        FROM erpoffer_prices o
        WHERE o.product_id=:PROD AND o.customer_id=:CST AND o.quantity=:QTY AND o.type=2 AND o.price=:PRICE";
        $params=[
        'PROD' => $product->getId(),
        'CST' => $customer->getId(),
        'QTY' => $qty,
        'PRICE' => $price
        ];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
     }
     else{
       $query="SELECT o.id as id
       FROM erpoffer_prices o
       WHERE o.product_id=:PROD AND o.customer_id IS NULL AND o.quantity=:QTY AND o.type=2 AND o.price=:PRICE";
       $params=[
       'PROD' => $product->getId(),
       'QTY' => $qty,
        'PRICE' => $price
       ];
       return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
     }

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
