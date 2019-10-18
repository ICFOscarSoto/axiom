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
    
    public function offerpricesByProduct($product){
      $query="SELECT c.name as CustomerGroup, p.increment as Increment, p.price as Price, p.start as Start, p.end as Endd
              FROM erpoffer_prices p LEFT JOIN erpcustomer_groups c ON c.id=p.customergroup_id 
              WHERE p.product_id=:PROD AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId()];
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
