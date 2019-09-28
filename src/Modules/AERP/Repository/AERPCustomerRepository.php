<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPCustomer|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPCustomer|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPCustomer[]    findAll()
 * @method AERPCustomer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPCustomerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPCustomer::class);
    }

    // /**
    //  * @return AERPCustomer[] Returns an array of AERPCustomer objects
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
    public function findOneBySomeField($value): ?AERPCustomer
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
