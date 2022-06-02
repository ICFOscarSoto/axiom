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

    public function dayVacations($worker, $day){
      $query="SELECT type, start, end, hourslastday from hrvacations
      WHERE worker_id = :worker AND (DATE(:start) BETWEEN DATE(START) AND DATE(END)) AND deleted=0 AND active=1
      AND approved=1
      ORDER BY start ASC LIMIT 1";

      $params=['worker' => $worker->getId(), 'start' => $day];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
    }


    public function getDiary($worker, $year){
      $query="SELECT id, DATE(start) start, DATE_ADD(DATE(end), INTERVAL 1 DAY) end, type, approved, workerobservations, companyobservations, days, hourslastday FROM hrvacations WHERE worker_id = :worker AND YEAR(start)=:year AND approved=1 AND deleted=0 AND active=1";
      $params=['worker' => $worker->getId(), 'year' => $year];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getByDates($from, $to){
      $query="SELECT h.id, u.id user_id, w.id worker_id, w.name, w.lastname, DATE(h.start) start, DATE_ADD(DATE(h.end), INTERVAL 1 DAY) end, h.type, h.approved, h.workerobservations, h.companyobservations, h.days, h.hourslastday
              FROM hrvacations h
              LEFT JOIN hrworkers w ON w.id=worker_id
              LEFT JOIN globale_users u ON u.id=user_id
              WHERE ((h.start BETWEEN :from AND :to) OR (h.end BETWEEN :from AND :to) OR (h.start <= :from AND h.start >= :to))
              AND h.approved=1 AND h.deleted=0 AND h.active=1";
      $params=['from' => $from, 'to' => $to];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
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
