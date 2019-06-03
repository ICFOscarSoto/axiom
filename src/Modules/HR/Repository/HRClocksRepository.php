<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRClocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRClocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRClocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRClocks[]    findAll()
 * @method HRClocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRClocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRClocks::class);
    }

    public function findWorkersClocks($company){
      /*$query="select hrc.id, hrw.id workerid, hrw.name, hrw.lastname, hrc.start, hrc.end FROM hrclocks hrc
                                LEFT JOIN hrworkers hrw ON hrc.worker_id=hrw.id
                                WHERE hrc.id=(SELECT max(id) from hrclocks WHERE deleted=0 AND worker_id=hrc.worker_id)
                                AND hrw.company_id = :company AND hrc.deleted=0 AND hrw.deleted=0
                                group by (hrc.worker_id) ORDER BY name, lastname ASC";*/

      $query="SELECT hrc.id, hrw.id workerid, hrw.name, hrw.lastname, hrc.start, hrc.end from hrworkers hrw
              LEFT JOIN (
              	SELECT m1.*
              	FROM hrclocks m1 LEFT JOIN hrclocks m2
               	ON (m1.worker_id = m2.worker_id AND m1.id < m2.id)
              	WHERE m2.id IS NULL) hrc ON hrc.worker_id=hrw.id
              WHERE hrw.company_id = :company AND hrw.deleted=0 AND hrw.active=1 ORDER BY lastname, name ASC";

      $params=['company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


    public function todayClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND START >= CURDATE() AND START < CURDATE() + INTERVAL 1 DAY";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function yesterdayClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated  FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND START >= CURDATE()-1 AND START < CURDATE()-1 + INTERVAL 1 DAY";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function thisWeekClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND WEEK(START) = WEEK(CURDATE())";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function lastWeekClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND WEEK(START) = WEEK(CURDATE() - INTERVAL 1 WEEK) ";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function thisMonthClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND MONTH(START) = MONTH(CURDATE())";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }
    public function lastMonthClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND MONTH(START) = MONTH(CURDATE() - INTERVAL 1 MONTH) ";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function thisYearClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND YEAR(START) = YEAR(CURDATE())";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }
    public function lastYearClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND YEAR(START) = YEAR(CURDATE() - INTERVAL 1 YEAR) ";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }
    /*
    public function findOneBySomeField($value): ?HRClocks
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
