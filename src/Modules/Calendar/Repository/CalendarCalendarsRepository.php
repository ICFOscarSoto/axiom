<?php

namespace App\Modules\Calendar\Repository;

use App\Modules\Calendar\Entity\CalendarCalendars;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Calendars|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendars|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendars[]    findAll()
 * @method Calendars[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarCalendarsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CalendarCalendars::class);
    }

    // /**
    //  * @return Calendars[] Returns an array of Calendars objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Calendars
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
