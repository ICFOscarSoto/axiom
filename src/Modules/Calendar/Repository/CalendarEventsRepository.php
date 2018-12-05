<?php

namespace App\Modules\Calendar\Repository;

use App\Modules\Calendar\Entity\CalendarEvents;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Events|null find($id, $lockMode = null, $lockVersion = null)
 * @method Events|null findOneBy(array $criteria, array $orderBy = null)
 * @method Events[]    findAll()
 * @method Events[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarEventsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CalendarEvents::class);
    }

    public function findByRange($id, $start, $end)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.start >= :start')
            ->andWhere('e.end <= :end')
            ->andWhere('e.calendar = :calendar')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('calendar', $id)
            ->orderBy('e.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Events
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
