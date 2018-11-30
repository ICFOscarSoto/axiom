<?php

namespace App\Modules\Email\Repository;

use App\Modules\Email\Entity\EmailFolders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EmailFolders|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailFolders|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailFolders[]    findAll()
 * @method EmailFolders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailFoldersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EmailFolders::class);
    }

    // /**
    //  * @return EmailFolders[] Returns an array of EmailFolders objects
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
    public function findOneBySomeField($value): ?EmailFolders
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
