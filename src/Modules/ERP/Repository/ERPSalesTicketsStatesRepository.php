<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSalesTicketsStates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSalesTicketsStates|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSalesTicketsStates|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSalesTicketsStates[]    findAll()
 * @method ERPSalesTicketsStates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSalesTicketsStatesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSalesTicketsStates::class);
    }

    // /**
    //  * @return ERPSalesTicketsStates[] Returns an array of ERPSalesTicketsStates objects
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
    public function findOneBySomeField($value): ?ERPSalesTicketsStates
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
