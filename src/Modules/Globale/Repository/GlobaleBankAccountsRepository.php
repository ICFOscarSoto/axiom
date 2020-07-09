<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleBankAccounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleBankAccounts|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleBankAccounts|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleBankAccounts[]    findAll()
 * @method GlobaleBankAccounts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleBankAccountsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleBankAccounts::class);
    }

    // /**
    //  * @return GlobaleBankAccounts[] Returns an array of GlobaleBankAccounts objects
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
    public function findOneBySomeField($value): ?GlobaleBankAccounts
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
