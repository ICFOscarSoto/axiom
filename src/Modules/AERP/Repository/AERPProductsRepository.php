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
