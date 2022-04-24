<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductsVariants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProductsVariants|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProductsVariants|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProductsVariants[]    findAll()
 * @method ERPProductsVariants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductsVariantsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProductsVariants::class);
    }

    // /**
    //  * @return ERPProductsVariants[] Returns an array of ERPProductsVariants objects
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
    public function findOneBySomeField($value): ?ERPProductsVariants
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /*
    * Obtiene las variantes de un producto
    */
    public function getWSProductVariants($product){
      $result = null;
      if ($product!=null){
        $result = [];
        $result['line'] = -1;
        $query="SELECT distinct(concat(vv.id,'~',concat(v.name, ' - ',vv.name))) as id,
                       concat(v.name, ' - ',vv.name) as name
                FROM erpproducts_variants pv
                LEFT JOIN erpvariants_values vv on pv.variantvalue_id=vv.id
                LEFT JOIN erpvariants v on vv.variantname_id=v.id
                LEFT JOIN erpproducts p ON pv.product_id=p.id
                WHERE p.code =:product
                ORDER BY name ASC";
        $aproduct = explode('~',$product);
        if (count($aproduct)>2){
          $result['line'] = $aproduct[0];
          $params=["product" => $aproduct[2]];
        }else
        if (count(explode('~',$product))>1){
          $result['line'] = $aproduct[0];
          $params=["product" => $aproduct[1]];
        }else
          $params=["product" => $product];
        $result['data'] = $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      }
      return $result;
    }

    /*
    * Obtiene el precio del producto que tiene un proveedor y su coste en función de la cantidad y la variante
    */
    public function getProductVariantBySupplier($supplier, $product, $variant, $quantity){
      $result = null;
      if ($product!='|'){
        $aproduct = explode('~',$product);
        if (count($aproduct)>1)
          $product = $aproduct[0];
        $avariant = explode('~',$variant);
        if (count($avariant)>1)
          $variant = $avariant[0];
        $query="SELECT if (sp.pvp is null, 0, if (sp.pvp='', 0, sp.pvp)) as 'pvp',
                       if (sd.discount1 is null, 0, if (sd.discount1='', 0, sd.discount1)) as 'discount1',
                       if (sd.discount2 is null, 0, if (sd.discount2='', 0, sd.discount2)) as 'discount2',
                       if (sd.discount3 is null, 0, if (sd.discount3='', 0, sd.discount3)) as 'discount3',
                       if (sd.discount4 is null, 0, if (sd.discount4='', 0, sd.discount4)) as 'discount4',
                       if (sd.discount is null, 0, if (sd.discount='', 0, sd.discount)) as 'discountequivalent',
                       if (sp.variant_id is null,0, sp.variant_id) as 'v'
                FROM erpshopping_prices sp LEFT JOIN
                     erpproducts p on p.id=sp.product_id LEFT JOIN
                     erpshopping_discounts sd on sd.supplier_id=sp.supplier_id and sd.category_id=p.category_id and sd.quantity<=:quantity and
                      sd.active=1 and sd.deleted=0 and
                      (sd.start is null or sd.start<=now()) and
                       (sd.end is null or sd.end>=now())
                WHERE sp.supplier_id=:supplier and
                      p.id=:product and
                      (sp.variant_id=:variant or sp.variant_id is null) and
                      sp.quantity<=:quantity and
                      p.active=1 and p.deleted=0 and
                      sp.active=1 and sp.deleted=0 and
                      (sp.start is null or sp.start<=now()) and
                      (sp.end is null or sp.end>=now())
                ORDER BY v DESC, sp.quantity DESC, sd.quantity DESC";
        $params=["supplier" => $supplier, "product" => $product, "variant" => $variant, "quantity"=>$quantity];
        $result = $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      }
      if ($result == null || count($result)==0)
        $result =
        [[
            "pvp" => "0",
            "discount1" => "0",
            "discount2" => "0",
            "discount3" => "0",
            "discount4" => "0",
            "discountequivalent" => "0",
            "v" => "0"
        ]];
      return $result;
    }
}
