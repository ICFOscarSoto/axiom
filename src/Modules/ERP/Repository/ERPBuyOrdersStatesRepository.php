<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyOrdersStates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyOrdersStates|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyOrdersStates|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyOrdersStates[]    findAll()
 * @method ERPBuyOrdersStates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyOrdersStatesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyOrdersStates::class);
    }

    // /**
    //  * @return ERPBuyOrdersStates[] Returns an array of ERPBuyOrdersStates objects
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
    public function findOneBySomeField($value): ?ERPBuyOrdersStates
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
