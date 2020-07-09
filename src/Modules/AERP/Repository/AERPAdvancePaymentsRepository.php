<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPAdvancePayments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPAdvancePayments|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPAdvancePayments|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPAdvancePayments[]    findAll()
 * @method AERPAdvancePayments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPAdvancePaymentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPAdvancePayments::class);
    }

    // /**
    //  * @return AERPAdvancePayments[] Returns an array of AERPAdvancePayments objects
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
    public function findOneBySomeField($value): ?AERPAdvancePayments
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
