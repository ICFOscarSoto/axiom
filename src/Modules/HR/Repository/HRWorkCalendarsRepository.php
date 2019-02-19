<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRWorkCalendars;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRWorkCalendars|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRWorkCalendars|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRWorkCalendars[]    findAll()
 * @method HRWorkCalendars[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRWorkCalendarsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRWorkCalendars::class);
    }

    // /**
    //  * @return HRWorkCalendars[] Returns an array of HRWorkCalendars objects
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
    public function findOneBySomeField($value): ?HRWorkCalendars
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
