<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProducts[]    findAll()
 * @method ERPProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProducts::class);
    }

    // /**
    //  * @return ERPProducts[] Returns an array of ERPProducts objects
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
    public function findOneBySomeField($value): ?ERPProducts
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function searchProduct($search){
      $tokens=explode('*',$search);
      $string='';
      foreach($tokens as $token){
        $string.=" AND (id='".$token."' OR name LIKE '%".$token."%' OR code LIKE '%".$token."%')";
      }
      //$query="SELECT id, code, name from erpproducts where active=1 and deleted=0".$string;
      $query="SELECT id, code, name, active from erpproducts where deleted=0".$string;
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }

    public function productsBySupplierCategory($supplier,$category){
      $query="SELECT id from erpproducts
      where supplier_id=:supplier AND category_id=:category";
      $params=['supplier' => $supplier,
              'category' => $category];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;

    }

    public function getVariants($product){
        $query='SELECT pv.id as id,v.name as type, vv.name as name
        FROM erpproducts_variants pv
        LEFT JOIN erpvariants_values vv
        ON pv.variantvalue_id=vv.id
        LEFT JOIN erpvariants v
        ON pv.variantname_id=v.id
        WHERE pv.product_id='.$product.' AND pv.active=1 AND pv.deleted=0 ORDER BY name ASC';
        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }


}
