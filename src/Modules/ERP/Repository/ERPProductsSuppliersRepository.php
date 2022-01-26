<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductsSuppliers;
use App\Modules\ERP\Entity\ERPConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProductsSuppliers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProductsSuppliers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProductsSuppliers[]    findAll()
 * @method ERPProductsSuppliers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductsSuppliersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProductsSuppliers::class);
    }

    // /**
    //  * @return ERPProductsSuppliers[] Returns an array of ERPProductsSuppliers objects
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
    public function findOneBySomeField($value): ?ERPProductsSuppliers
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getDiscountSuppliersByProduct($user, $doctrine, $product)
    {
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
                     sd.discount as discount,
                     sd.quantity as quantity,
                     sd.start as start,
                     sd.end as end,
                     round(sp.pvp,2) as PVPR,
                     round(sp.shopping_price,:decimals) as price,
                     (case when s.id=:supplier_preference then 1 else 0 end) as preference
              FROM erpproducts_suppliers ps LEFT JOIN
                   erpsuppliers s on s.id=ps.supplier_id LEFT JOIN
                   erpshopping_discounts sd on sd.supplier_id=ps.supplier_id and sd.category_id=:category and sd.deleted=0 and sd.active=1 LEFT JOIN
                   erpshopping_prices sp on sp.product_id=:product and sp.supplier_id=ps.supplier_id and (sp.quantity=sd.quantity or sd.quantity is null)
              WHERE ps.product_id=:product and
                    s.deleted=0 and s.active=1 and
                    ps.deleted=0 and ps.active=1 and
                    ps.company_id = :company and
                    s.company_id = :company
              ORDER BY preference desc, supplier asc";
      $param=["product"=>$product_id, "category"=>$category_id, "supplier_preference"=>$supplierPreference, "company"=>$company->getId(), "decimals"=>$config->getDecimals()];
      $suppliers = $this->getEntityManager()->getConnection()->executeQuery($query, $param)->fetchAll();
      return $suppliers;
    }
}
