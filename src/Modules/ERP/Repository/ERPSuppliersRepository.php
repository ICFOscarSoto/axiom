<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSuppliers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSuppliers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSuppliers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSuppliers[]    findAll()
 * @method ERPSuppliers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSuppliersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSuppliers::class);
    }


    public function findById($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function productsBySupplier($supplier){
      $query="SELECT id from erpproducts
      where supplier_id=:supplier";
      $params=['supplier' => $supplier];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }
}
