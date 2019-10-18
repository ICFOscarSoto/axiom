<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProductPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProductPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProductPrices[]    findAll()
 * @method ERPProductPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProductPrices::class);
    }
    
    public function pricesByProduct($product){
      $query="SELECT c.name as CustomerGroup, p.increment as Increment, p.price as Price
              FROM erpproduct_prices p LEFT JOIN erpcustomer_groups c ON c.id=p.customergroup_id 
              WHERE p.product_id=:PROD AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }
    
    public function pricesByProductId($product){
      $query="SELECT id from erpproduct_prices
      where product_id=:product";
      $params=['product' => $product];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }
    
    public function existPrice($product,$customergroup){
      $query="SELECT * FROM erpproduct_prices p WHERE p.product_id=:PROD AND p.customergroup_id=:GRP AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId(),
               'GRP' => $customergroup->getId(),
        ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
    //  dump($result);
      if($result!=NULL) return true;
      else return false;
    }
    
}
