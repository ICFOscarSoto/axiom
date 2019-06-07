<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPContacts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPContacts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPContacts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPContacts[]    findAll()
 * @method ERPContacts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPContactsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPContacts::class);
    }

    // /**
    //  * @return ERPContacts[] Returns an array of ERPContacts objects
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
    public function findOneBySomeField($value): ?ERPContacts
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
