<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPWarehouses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPWarehouses|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPWarehouses|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPWarehouses[]    findAll()
 * @method AERPWarehouses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPWarehousesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPWarehouses::class);
    }

    // /**
    //  * @return AERPWarehouses[] Returns an array of AERPWarehouses objects
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
    public function findOneBySomeField($value): ?AERPWarehouses
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
