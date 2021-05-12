<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoreTicketsStates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoreTicketsStates|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoreTicketsStates|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoreTicketsStates[]    findAll()
 * @method ERPStoreTicketsStates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoreTicketsStatesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoreTicketsStates::class);
    }

    // /**
    //  * @return ERPStoreTicketsStates[] Returns an array of ERPStoreTicketsStates objects
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
    public function findOneBySomeField($value): ?ERPStoreTicketsStates
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
