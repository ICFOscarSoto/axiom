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

    public function findWorkersClocks($company, $department=0, $workcenter=0){
      /*$query="SELECT hrc.id, hrw.id workerid, hrw.name, hrw.lastname, hrc.start, hrc.end from hrworkers hrw
              LEFT JOIN (
              	SELECT m1.*
              	FROM hrclocks m1 LEFT JOIN hrclocks m2
               	ON (m1.worker_id = m2.worker_id AND m1.id < m2.id)
              	WHERE m2.id IS NULL AND m1.deleted=0 AND m1.active=1 AND m1.invalid<>1) hrc ON hrc.worker_id=hrw.id
              WHERE hrw.company_id = :company AND hrw.deleted=0 AND hrw.active=1 ";*/
      $query="SELECT hrc.id, hrw.id workerid, hrw.name, hrw.lastname, hrc.start, hrc.end from hrworkers hrw
              LEFT JOIN (
              	SELECT m1.*
              	FROM hrclocks m1 LEFT JOIN hrclocks m2
               	ON (m1.worker_id = m2.worker_id AND m1.start < m2.start)
              	WHERE m2.id IS NULL AND m1.deleted=0 AND m1.active=1 AND m1.invalid<>1) hrc ON hrc.worker_id=hrw.id
              WHERE hrw.company_id = :company AND hrw.deleted=0 AND hrw.active=1 ";
      if($department!=0) $query.=" AND hrw.department_id=".$department;
      if($workcenter!=0) $query.=" AND hrw.workcenters_id=".$workcenter;
      $query.=" ORDER BY lastname, name ASC";

      $params=['company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function findOpenByCompany($company_id, $workcenter_id=null, $department_id=null){
        $query="SELECT c.*, w.name, w.lastname FROM hrclocks c
	               LEFT JOIN hrworkers w ON c.worker_id=w.id
	               WHERE c.end IS NULL AND (c.invalid <> 1 ) AND c.deleted=0 AND w.company_id=:company_id";

        if($workcenter_id!=null) $query.=" AND w.workcenters_id=:workcenters_id";
        if($department_id!=null) $query.=" AND w.department_id=:department_id";
        $params=['company_id' => $company_id, 'workcenters_id' => $workcenter_id, "department_id"=> $department_id];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


    public function todayClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND DATE(START) >= CURDATE() AND DATE(START) < CURDATE() + INTERVAL 1 DAY";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function yesterdayClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated  FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND DATE(START) >= (CURDATE()-INTERVAL 1 DAY) AND DATE(START) < CURDATE()";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function thisWeekClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND WEEK(START,1) = WEEK(CURDATE(),1)";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function lastWeekClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND WEEK(START,1) = WEEK(CURDATE() - INTERVAL 1 WEEK,1) ";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function thisMonthClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND MONTH(START) = MONTH(CURDATE())";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }
    public function lastMonthClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND MONTH(START) = MONTH(CURDATE() - INTERVAL 1 MONTH) ";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function thisYearClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND YEAR(START) = YEAR(CURDATE())";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }
    public function lastYearClocks($worker){
      $conn = $this->getEntityManager()->getConnection();
      $sql = "SELECT IFNULL(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START)),0) raw, IFNULL(SEC_TO_TIME(SUM(UNIX_TIMESTAMP(IFNULL(END,CURTIME()))-UNIX_TIMESTAMP(START))),'00:00:00') formated FROM hrclocks
              WHERE worker_id=? AND deleted=0 AND active=1 AND invalid<>1 AND YEAR(START) = YEAR(CURDATE() - INTERVAL 1 YEAR) ";
      $stmt = $conn->prepare($sql);
      $stmt->bindValue(1, $worker->getId());
      $stmt->execute();
      return $stmt->fetch();
    }

    public function dayClocks($worker, $day){

      $query="SELECT TIME(start) start, TIME(end) end, time, observations, invalid from hrclocks
              WHERE worker_id = :worker AND DATE(start) = :start AND deleted=0 AND active=1 AND invalid<>1
              ORDER BY start ASC";

      $params=['worker' => $worker->getId(), 'start' => $day];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
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
