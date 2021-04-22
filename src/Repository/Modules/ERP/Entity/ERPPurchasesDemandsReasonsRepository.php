<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPPurchasesDemandsReasons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPurchasesDemandsReasons|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPurchasesDemandsReasons|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPurchasesDemandsReasons[]    findAll()
 * @method ERPPurchasesDemandsReasons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPurchasesDemandsReasonsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPurchasesDemandsReasons::class);
    }

    // /**
    //  * @return ERPPurchasesDemandsReasons[] Returns an array of ERPPurchasesDemandsReasons objects
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
    public function findOneBySomeField($value): ?ERPPurchasesDemandsReasons
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
