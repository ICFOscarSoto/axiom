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

    public function productsBySupplierCategory($supplier,$category){
      $query="SELECT id from erpproducts
      where supplier_id=:supplier AND category_id=:category";
      $params=['supplier' => $supplier,
              'category' => $category];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;



    }


}
