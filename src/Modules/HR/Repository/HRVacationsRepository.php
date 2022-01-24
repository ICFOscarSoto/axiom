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
      $query="SELECT id, DATE(start) start, DATE(end) end, type, approved, workerobservations, companyobservations, days, hourslastday FROM hrvacations WHERE worker_id = :worker AND YEAR(start)=:year AND approved=1 AND deleted=0 AND active=1";
      $params=['worker' => $worker->getId(), 'year' => $year];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
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
