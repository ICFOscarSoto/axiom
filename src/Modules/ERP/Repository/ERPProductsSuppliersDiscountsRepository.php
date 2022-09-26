<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductsSuppliersDiscounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProductsSuppliersDiscounts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProductsSuppliersDiscounts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProductsSuppliersDiscounts[]    findAll()
 * @method ERPProductsSuppliersDiscounts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductsSuppliersDiscountsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProductsSuppliersDiscounts::class);
    }

    public function deleteShoppingDiscount($id){
      $query='DELETE FROM erpproducts_suppliers_discounts
      where id=:id';
      $params=['id' => $id];
      $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getShoppingDiscounts($category,$supplier){
      $query="SELECT quantity, discount from erpproducts_suppliers_discounts
      where supplier_id=:supplier AND category_id=:category and active=1 and deleted=0 and end>CURDATE()";
      $params=['supplier' => $supplier,
              'category' => $category];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;
    }

    // /**
    //  * @return ERPProductsSuppliersDiscounts[] Returns an array of ERPProductsSuppliersDiscounts objects
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
    public function findOneBySomeField($value): ?ERPProductsSuppliersDiscounts
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
