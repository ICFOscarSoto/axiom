<?php

namespace App\Repository\Email;

use App\Entity\Email\EmailSubjects;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EmailSubjects|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailSubjects|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailSubjects[]    findAll()
 * @method EmailSubjects[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailSubjectsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EmailSubjects::class);
    }

    // /**
    //  * @return EmailSubjects[] Returns an array of EmailSubjects objects
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
    public function findOneBySomeField($value): ?EmailSubjects
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
