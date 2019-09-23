<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRSchedules;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRSchedules|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRSchedules|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRSchedules[]    findAll()
 * @method HRSchedules[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRSchedulesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRSchedules::class);
    }


    public function mustWork($worker, $date, $time){
      $schedule=$worker->getSchedule();
      if($schedule==null) return false;
      //Get day of week
      $dayWeek=date("l", strtotime($date));
      //Depend of schedule type
      switch($schedule->getType()){
        case 1: //Libre
          return null;
        break;

        case 2: //Fijo
          $query="SELECT p.* FROM hrperiods p
                  	LEFT JOIN hrshifts s ON p.shift_id=s.id
                  	LEFT JOIN hrschedules sh ON s.schedule_id=sh.id
                  	LEFT JOIN hrworkers w ON sh.id=w.schedule_id
                  WHERE
                  	w.id=:WORKERID AND
                  	(STR_TO_DATE(:DATE, '%Y-%m-%d') BETWEEN STR_TO_DATE(CONCAT(p.fromdate,'/', YEAR(CURDATE())), '%d/%m/%Y') AND STR_TO_DATE(CONCAT(p.todate,'/', YEAR(CURDATE())), '%d/%m/%Y')) AND
                  	(STR_TO_DATE(:TIME, '%H:%i:%s') BETWEEN STR_TO_DATE(p.start, '%H:%i:%s') AND STR_TO_DATE(p.end, '%H:%i:%s')) AND
                  	p.Monday=1 AND	p.active=1 AND p.deleted=0 AND s.active=1 AND s.deleted=0 AND sh.active=1 AND sh.deleted=0 LIMIT 1";
                    $params=['WORKERID' => $worker->getId(),
                             'DATE' => $date,
                             'TIME' => $time];
          $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
          return $result;
        break;

        case 3: //Rotativo
          //day of YEAR
          $dayYear=date('z', strtotime($date)) + 1;
          $query="SELECT COUNT(*) shifts FROM hrshifts s WHERE s.schedule_id=:SCHEDULEID";
          $result=$this->getEntityManager()->getConnection()->executeQuery($query, ["SCHEDULEID"=>$schedule->getId()])->fetch();
          $shiftsCount=$result["shifts"];
          $groupWork=ceil($dayYear/$schedule->getPeriod())%$shiftsCount; //SELECT NUM OF SHIFT
          $query="SELECT * FROM hrshifts s WHERE s.schedule_id=:SCHEDULEID LIMIT ".$groupWork.",1";
          $shift=$this->getEntityManager()->getConnection()->executeQuery($query, ["SCHEDULEID"=>$schedule->getId()])->fetch();
          $query="SELECT p.* FROM hrperiods p
                  	LEFT JOIN hrshifts s ON p.shift_id=s.id
                  WHERE
                  	s.id=:SHIFTID AND
                  	(STR_TO_DATE(:DATE, '%Y-%m-%d') BETWEEN STR_TO_DATE(CONCAT(p.fromdate,'/', YEAR(CURDATE())), '%d/%m/%Y') AND STR_TO_DATE(CONCAT(p.todate,'/', YEAR(CURDATE())), '%d/%m/%Y')) AND
                  	(STR_TO_DATE(:TIME, '%H:%i:%s') BETWEEN STR_TO_DATE(p.start, '%H:%i:%s') AND STR_TO_DATE(p.end, '%H:%i:%s')) AND
                  	p.Monday=1 AND	p.active=1 AND p.deleted=0 AND s.active=1 AND s.deleted=0 LIMIT 1";
                    $params=['SHIFTID' => $shift["id"],
                             'DATE' => $date,
                             'TIME' => $time];
          $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
          return $result;

        break;

      }
    }
}
