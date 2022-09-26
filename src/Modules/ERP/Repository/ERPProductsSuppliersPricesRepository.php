<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductsSuppliersPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProductsSuppliersPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProductsSuppliersPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProductsSuppliersPrices[]    findAll()
 * @method ERPProductsSuppliersPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductsSuppliersPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProductsSuppliersPrices::class);
    }

    // /**
    //  * @return ERPProductsSuppliersPrices[] Returns an array of ERPProductsSuppliersPrices objects
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

    public function getProductsSuppliersPrices($product,$supplier){
      $query="SELECT quantity, price, pvp from erpproducts_suppliers_prices psp left join erpproducts_suppliers ps on ps.id=psp.productsupplier_id left join erpproducts_variants pv on pv.id=ps.productvariant_id
      where ps.supplier_id=:supplier AND pv.product_id=:product and pv.variant_id is null and psp.active=1 and psp.deleted=0 and (psp.end is null or psp.end>CURDATE())";
      $params=['supplier' => $supplier,
              'product' => $product];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;

    }

    /*
    public function findOneBySomeField($value): ?ERPProductsSuppliersPrices
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
