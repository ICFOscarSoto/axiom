<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRVacations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRVacations|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRVacations|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRVacations[]    findAll()
 * @method HRVacations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRVacationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRVacations::class);
    }

    // /**
    //  * @return HRVacations[] Returns an array of HRVacations objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HRVacations
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
