<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerPrices;
use App\Modules\ERP\Entity\ERPConfiguration;
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

    public function pricesByProductSupplier($user, $doctrine, $product){
      $configurationRepository  = $doctrine->getRepository(ERPConfiguration::class);
      $company                  = $user->getCompany();
      $config                   = $configurationRepository->findOneBy(["company"=>$company, "active"=>1, "deleted"=>0]);

      $product_id = $product->getId();
      // Proveedor preferente
      $supplierPreference = 0;
      if ($product->getSupplier()!=null)
        $supplierPreference=$product->getSupplier()->getId();
      // Categoria
      $category_id = 0;
      if ($product->getCategory()!=null)
        $category_id=$product->getCategory()->getId();

      $query="SELECT ps.supplier_id as supplier_id,
                     concat('(',s.code,')',' ',s.name) as supplier,
                     concat('(',cu.code,')',' ',cu.name) as customer,
                     cu.id as customer_id,
                     i.increment as Increment,
                     i.start as start,
                     i.end as end,
                     round((psp.price * (1+(i.increment/100))),:decimals) as price,
                     (case when s.id=:supplier_preference then 1 else 0 end) as preference
              FROM erpproducts_suppliers ps LEFT JOIN
                   erpproducts_variants pv on pv.id=ps.productvariant_id LEFT JOIN
                   erpsuppliers s on s.id=ps.supplier_id LEFT JOIN
                   erpproducts_suppliers_prices psp on psp.productsupplier_id=ps.id and psp.quantity=1 LEFT JOIN
                   erpcustomer_increments i on i.supplier_id=ps.supplier_id and i.category_id=:category and i.deleted=0 and i.active=1 LEFT JOIN
                   erpcustomers cu on cu.id=i.customer_id
              WHERE pv.product_id=:product and
                    pv.variant_id is null and
                    s.deleted=0 and s.active=1 and
                    ps.deleted=0 and ps.active=1 and
                    ps.company_id = :company and
                    s.company_id = :company and
                    i.company_id = :company and
                    cu.company_id = :company
              ORDER BY preference desc, s.name asc, cu.name asc";
      $param=["product"=>$product_id, "category"=>$category_id, "supplier_preference"=>$supplierPreference, "company"=>$company->getId(), "decimals"=>$config->getDecimals()];
      $result = $this->getEntityManager()->getConnection()->executeQuery($query, $param)->fetchAll();
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
