<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRPeriods;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRPeriods|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRPeriods|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRPeriods[]    findAll()
 * @method HRPeriods[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRPeriodsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRPeriods::class);
    }

    public function findByPeriod($period){
      $query="SELECT * FROM hrperiods p WHERE
            	((STR_TO_DATE(CONCAT(:FROMDATE,'/', YEAR(CURDATE())), '%d/%m/%Y') BETWEEN STR_TO_DATE(CONCAT(p.fromdate,'/', YEAR(CURDATE())), '%d/%m/%Y') AND STR_TO_DATE(CONCAT(p.todate,'/', YEAR(CURDATE())), '%d/%m/%Y')) OR
            	(STR_TO_DATE(CONCAT(:TODATE,'/', YEAR(CURDATE())), '%d/%m/%Y') BETWEEN STR_TO_DATE(CONCAT(p.fromdate,'/', YEAR(CURDATE())), '%d/%m/%Y') AND STR_TO_DATE(CONCAT(p.todate,'/', YEAR(CURDATE())), '%d/%m/%Y'))) AND
            	((STR_TO_DATE(:START, '%H:%i:%s') BETWEEN STR_TO_DATE(p.start, '%H:%i:%s') AND STR_TO_DATE(p.end, '%H:%i:%s')) OR
            	(STR_TO_DATE(:END, '%H:%i:%s') BETWEEN STR_TO_DATE(p.start, '%H:%i:%s') AND STR_TO_DATE(p.end, '%H:%i:%s')) OR
            	(STR_TO_DATE(:START, '%H:%i:%s')<STR_TO_DATE(p.start, '%H:%i:%s') AND STR_TO_DATE(:END, '%H:%i:%s')>STR_TO_DATE(p.end, '%H:%i:%s'))) and
              (
              	(p.monday=1 AND p.monday=:MONDAY) OR
              	(p.tuesday=1 AND p.tuesday=:TUESDAY) OR
              	(p.wednesday=1 AND p.wednesday=:WEDNESDAY) OR
              	(p.thursday=1 AND p.thursday=:THURSDAY) OR
              	(p.friday=1 AND p.friday=:FRIDAY) OR
              	(p.saturday=1 AND p.saturday=:SATURDAY) OR
              	(p.sunday=1 AND p.sunday=:SUNDAY)
              )
              AND p.active=1 AND p.deleted=0 AND shift_id=:SHIFT AND id<>:ID";

      $params=['ID' => $period->getId()!=null?$period->getId():0,
               'FROMDATE' => $period->getFromdate(),
               'TODATE' => $period->getTodate(),
               'START' => $period->getStart(),
               'END' => $period->getEnd(),
               'MONDAY' => $period->getMonday(),
               'TUESDAY' => $period->getTuesday(),
               'WEDNESDAY' => $period->getWednesday(),
               'THURSDAY' => $period->getThursday(),
               'FRIDAY' => $period->getFriday(),
               'SATURDAY' => $period->getSaturday(),
               'SUNDAY' => $period->getSunday(),
               'SHIFT' => $period->getShift()->getId() ];

      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();

    }
}
