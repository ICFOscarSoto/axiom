<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRSickleaves;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRSickleaves|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRSickleaves|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRSickleaves[]    findAll()
 * @method HRSickleaves[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRSickleavesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRSickleaves::class);
    }

    public function daySickleave($worker, $day){
      $query="SELECT type,name from hrsickleaves
      WHERE worker_id = :worker AND (DATE(:start) BETWEEN DATE(START) AND DATE(END)) AND deleted=0 AND active=1
      AND justified=1
      ORDER BY start ASC LIMIT 1";

      $params=['worker' => $worker->getId(), 'start' => $day];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
    }

    public function getDiary($worker, $year){
      $query="SELECT id, DATE(start) start, DATE_ADD(DATE(end), INTERVAL 1 DAY) end, type, justified, workerobservations, companyobservations, days FROM hrsickleaves WHERE worker_id = :worker AND (YEAR(start)=:year OR YEAR(end)=:year) AND deleted=0 AND active=1";
      $params=['worker' => $worker->getId(), 'year' => $year];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    // /**
    //  * @return HRSickleaves[] Returns an array of HRSickleaves objects
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
    public function findOneBySomeField($value): ?HRSickleaves
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
