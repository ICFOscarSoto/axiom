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
      dump($query);
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }
    
    public function exists($product,$customergroup){
      $query="SELECT * FROM erpproduct_prices p WHERE p.product_id=:PROD AND p.customergroup_id=:GRP AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId(),
               'GRP' => $customergroup->getId(),
        ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
      if($result!=NULL) return true;
      else return false;
    }
    

    // /**
    //  * @return ERPProductPrices[] Returns an array of ERPProductPrices objects
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
    public function findOneBySomeField($value): ?ERPProductPrices
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
