<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPProducts[]    findAll()
 * @method AERPProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPProductsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPProducts::class);
    }

    public function getNextAccounting($company)
    {
      $query="SELECT IFNULL(MAX(accountingaccount)+1,30000001)accountingaccount FROM aerpproducts WHERE company_id=:company";
      $params=['company' => $company];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
    }

    public function getProductPrice($product, $customergroup, $company){
      //TODO check if exist a offer

      //Check if group has a special price
      if($customergroup!=0){
        $query="SELECT total FROM aerpcustomer_groups_prices WHERE customergroup_id=:customergroup AND product_id=:product AND company_id=:company AND active=1 AND deleted=0";
        $params=['product' => $product, 'customergroup' => $customergroup, 'company' => $company];
        $price=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
        if($price!==null && !is_bool($price)) return $price;
      }
      //Get the pvp price
      $query="SELECT price FROM aerpproducts WHERE id=:product AND company_id=:company AND active=1 AND deleted=0";
      $params=['product' => $product, 'company' => $company];
      $price=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
      return $price;
    }

    // /**
    //  * @return AERPProducts[] Returns an array of AERPProducts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AERPProducts
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
