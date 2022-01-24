<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRClocksDiary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRClocksDiary|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRClocksDiary|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRClocksDiary[]    findAll()
 * @method HRClocksDiary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRClocksDiaryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRClocksDiary::class);
    }

    public function getDiary($worker, $year){

      $query="SELECT id, DATE(date) start, time, estimatedtime, difftime, excludedifftime FROM hrclocks_diary WHERE worker_id = :worker AND YEAR(date)=:year AND deleted=0 AND active=1";
      $params=['worker' => $worker->getId(), 'year' => $year];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
    // /**
    //  * @return HRClocksDiary[] Returns an array of HRClocksDiary objects
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
    public function findOneBySomeField($value): ?HRClocksDiary
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
