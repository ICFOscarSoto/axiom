<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRHollidays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRHollidays|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRHollidays|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRHollidays[]    findAll()
 * @method HRHollidays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRHollidaysRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRHollidays::class);
    }

    public function dayHolliday($worker, $year, $day){
      $query="SELECT h.name, h.type FROM hrhollidays h
            	LEFT JOIN hrwork_calendars c ON h.calendar_id=c.id
            	LEFT JOIN hrwork_calendar_groups g ON c.workcalendargroup_id=g.id
            	WHERE c.YEAR=:year AND DATE(h.DATE)=:date AND g.id=:calendar";
      if($worker->getWorkcalendarGroup()==null) return null;
      $params=['year' => $year,'calendar' => $worker->getWorkcalendarGroup()->getId(), 'date' => $day];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
    }

/*
SELECT h.name FROM hrhollidays h
	LEFT JOIN hrwork_calendars c ON h.calendar_id=c.id
	LEFT JOIN hrwork_calendar_groups g ON c.workcalendargroup_id=g.id

	WHERE c.YEAR=2019 AND DATE(h.DATE)="2019-05-01" AND g.id

  */


    // /**
    //  * @return HRHollidays[] Returns an array of HRHollidays objects
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
    public function findOneBySomeField($value): ?HRHollidays
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
