<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPPaymentMethods;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPaymentMethods|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPaymentMethods|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPaymentMethods[]    findAll()
 * @method ERPPaymentMethods[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPaymentMethodsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPaymentMethods::class);
    }

    // /**
    //  * @return ERPPaymentMethods[] Returns an array of ERPPaymentMethods objects
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
    public function findOneBySomeField($value): ?ERPPaymentMethods
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
