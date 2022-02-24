<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPShoppingPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPShoppingPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPShoppingPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPShoppingPrices[]    findAll()
 * @method ERPShoppingPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPShoppingPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPShoppingPrices::class);
    }

    // /**
    //  * @return ERPShoppingPrices[] Returns an array of ERPShoppingPrices objects
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

    public function getShoppingPrices($product,$supplier){
      $query="SELECT quantity, shopping_price, pvp from erpshopping_prices
      where supplier_id=:supplier AND product_id=:product and active=1 and deleted=0 and (end is null or end>CURDATE())";
      $params=['supplier' => $supplier,
              'product' => $product];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;

    }

    /*
    public function findOneBySomeField($value): ?ERPShoppingPrices
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
