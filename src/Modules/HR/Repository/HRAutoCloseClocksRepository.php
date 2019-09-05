<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRAutoCloseClocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRAutoCloseClocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRAutoCloseClocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRAutoCloseClocks[]    findAll()
 * @method HRAutoCloseClocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRAutoCloseClocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRAutoCloseClocks::class);
    }

    public function findByTime($value){
      $query="SELECT companyown_id as company_id, workcenter_id, department_id, time from hrauto_close_clocks
      WHERE DATE_FORMAT(STR_TO_DATE(time, '%H:%i:%s'), '%H:%i')=:time AND deleted=0 AND active=1 ORDER BY company_id ASC";
      $params=['time' => $value];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    // /**
    //  * @return HRAutoCloseClocks[] Returns an array of HRAutoCloseClocks objects
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
    public function findOneBySomeField($value): ?HRAutoCloseClocks
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
