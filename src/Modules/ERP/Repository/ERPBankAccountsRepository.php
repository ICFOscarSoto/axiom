<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBankAccounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBankAccounts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBankAccounts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBankAccounts[]    findAll()
 * @method ERPBankAccounts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBankAccountsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBankAccounts::class);
    }

    // /**
    //  * @return ERPBankAccounts[] Returns an array of ERPBankAccounts objects
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
    public function findOneBySomeField($value): ?ERPBankAccounts
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
