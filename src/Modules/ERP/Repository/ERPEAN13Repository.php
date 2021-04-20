<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPEAN13;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPEAN13|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPEAN13|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPEAN13[]    findAll()
 * @method ERPEAN13[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPEAN13Repository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPEAN13::class);
    }

    public function totalEAN13(){
      $query='SELECT count(id) as total from erpEAN13 where active!=2';
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result[0]["total"];
    }

    public function EAN13Limit($start, $page){
      $query='SELECT id FROM erpEAN13
              WHERE active=!2
              ORDER BY name
              LIMIT '.$start.','.$page.'';
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }

    // /**
    //  * @return ERPEAN13[] Returns an array of ERPEAN13 objects
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
    public function findOneBySomeField($value): ?ERPEAN13
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
