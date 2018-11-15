<?php

namespace App\Repository\Email;

use App\Entity\Email\EmailAccounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EmailAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailAccount[]    findAll()
 * @method EmailAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailAccountsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EmailAccounts::class);
    }

    // /**
    //  * @return EmailAccount[] Returns an array of EmailAccount objects
    //  */

    public function findByUserId($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.user = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?EmailAccount
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
