<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductPrices;
use App\Modules\ERP\Entity\ERPConfiguration;
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
                     c.name as CustomerGroup,
                     i.increment as Increment,
                     round(sp.shopping_price,:decimals) as pricecost,
                     round((sp.shopping_price * (1+(i.increment/100))),:decimals) as price,
                     (case when s.id=:supplier_preference then 1 else 0 end) as preference
              FROM erpproducts_suppliers ps LEFT JOIN
                   erpsuppliers s on s.id=ps.supplier_id LEFT JOIN
                   erpshopping_prices sp on sp.product_id=:product and sp.supplier_id=ps.supplier_id and sp.quantity=1 LEFT JOIN
                   erpincrements i on i.supplier_id=ps.supplier_id and i.category_id=:category and i.deleted=0 and i.active=1 LEFT JOIN
                   erpcustomer_groups c ON c.id=i.customergroup_id
              WHERE ps.product_id=:product and
                    s.deleted=0 and s.active=1 and
                    ps.deleted=0 and ps.active=1 and
                    ps.company_id = :company and
                    s.company_id = :company and
                    i.company_id = :company and
                    c.company_id = :company
              ORDER BY preference desc, s.name asc, CustomerGroup desc";
      $param=["product"=>$product_id, "category"=>$category_id, "supplier_preference"=>$supplierPreference, "company"=>$company->getId(), "decimals"=>$config->getDecimals()];
      $result = $this->getEntityManager()->getConnection()->executeQuery($query, $param)->fetchAll();
      return $result;
    }

    public function pricesByProductId($product){
      $query="SELECT id from erpproduct_prices
      where product_id=:product";
      $params=['product' => $product];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }

    public function getPriceforGroup($product,$group){
      $query="SELECT price FROM erpproduct_prices
      WHERE product_id=:product AND customergroup_id=:group AND active=1 AND deleted=0";
      $params=['product' => $product->getId(),
              'group' => $group->getId()
              ];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn();
    }


      public function pricesByProductIdAndSupplier($product,$supplier){
          $query="SELECT id from erpproduct_prices
          WHERE product_id=:product AND supplier_id=:supplier";
          $params=['product' => $product,
                  'supplier' => $supplier
                  ];
          return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

      }


    public function existPrice($product,$customergroup,$supplier){
      $query="SELECT * FROM erpproduct_prices p WHERE p.product_id=:PROD AND p.customergroup_id=:GRP AND p.supplier_id=:SUP AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId(),
               'GRP' => $customergroup->getId(),
               'SUP' => $supplier->getId(),
        ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
    //  dump($result);
      if($result!=NULL) return true;
      else return false;
    }


//lamamos a este método cuando hay un cambio en el incremento general de un grupo de descuento. En esto caso, no distinguimos
//por proveedor, sino que actualizamos todos los precios para ese grupo de descuento.
    public function existPriceByCustomerGroup($product,$customergroup){
      $query="SELECT * FROM erpproduct_prices p WHERE p.product_id=:PROD AND p.customergroup_id=:GRP AND p.supplier_id IS NULL AND p.active=TRUE and p.deleted=0";
      $params=['PROD' => $product->getId(),
               'GRP' => $customergroup->getId()
        ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
    //  dump($result);
      if($result!=NULL) return true;
      else return false;
    }

    //Llamamos a este método cuando hay algún cambio de incrementos en la tabla de CustomerGroups.
    //Lo que hacemos es buscar cualquier tipo de precio para un producto y un proveedor. En caso de que exista,
    //este incremento de precio general no le afectará.
    public function existPriceByProductSupplier($product,$supplier){
          $query="SELECT * FROM erpproduct_prices p WHERE p.product_id=:PROD AND p.supplier_id=:SUP AND p.active=TRUE and p.deleted=0";
          $params=['PROD' => $product->getId(),
                   'SUP' => $supplier->getId()
            ];
          $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
        //  dump($result);
          if($result!=NULL) return true;
          else return false;
        }


}
