<?php
namespace App\Modules\HR\Helpers;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\HR\Entity\HRClocksDiary;
use App\Modules\HR\Entity\HRSchedulesWorkers;
use App\Modules\HR\Entity\HRSchedules;
use App\Modules\HR\Entity\HRShifts;
use App\Modules\HR\Entity\HRPeriods;
use App\Modules\HR\Entity\HRHollidays;
use App\Modules\HR\Entity\HRWorkCalendarGroups;
use App\Modules\HR\Entity\HRWorkCalendars;
use App\Modules\HR\Entity\HRSickleaves;
use App\Modules\HR\Entity\HRVacations;

class HelperClocks{
  /*
  Return true if date is a holliday in the worker work calendar if exist
  */
  public function isHoliday($date, $worker, $doctrine){
    $workCalendarsrepository =$doctrine->getRepository(HRWorkCalendars::class);
    $hollidaysrepository     =$doctrine->getRepository(HRHollidays::class);
    $workCalendar=$workCalendarsrepository->findOneBy(["workcalendargroup"=>$worker->getWorkcalendargroup(),"year"=>$date->format('Y'), "active"=>1, "deleted"=>0]);
    if($workCalendar!=null){
      $holliday=$hollidaysrepository->findOneBy(["calendar"=>$workCalendar, "date"=>$date, "active"=>1, "deleted"=>0]);
      if($holliday!=null) return true;
    }
    return false;
  }

  /*
  Return true if date is a sick leave of the worker
  */
  public function isSickLeave($date, $worker, $doctrine){
    $sickLeavesRepository=$doctrine->getRepository(HRSickleaves::class);
    $sickLeaves=$sickLeavesRepository->daySickleave($worker, $date->format('Y-m-d'));
    if($sickLeaves) return true;
      else return false;
  }

  /*
  Return true if date is a vacation of the worker or the amount of vacation hours of the day
  */
  public function isVacation($date, $worker, $doctrine){
    $vacationsRepository=$doctrine->getRepository(HRVacations::class);
    $vacations=$vacationsRepository->dayVacations($worker, $date->format('Y-m-d'));
    if($vacations){
      //check if is lastday of vacations
       $lastday=date_create_from_format('Y-m-d H:i:s',$vacations["end"]);
       if($vacations==$date && $vacations["ourlastday"]!=0 && $vacations["ourlastday"]!=null){
           return $vacations["ourlastday"];
       }else return true;
     }else return false;
  }


  /*
  Return the estimated working time for the worker in date
  NOTE: does not take into account holidays, sick leaves, vacations, etc. only the schedule
  */
  public function estimatedTimeDay($date, $worker, $doctrine){
    $schedulesWorkersRepository  =$doctrine->getRepository(HRSchedulesWorkers::class);
    $scheduleShiftsRepository    =$doctrine->getRepository(HRShifts::class);
    $schedulePeriodsRepository   =$doctrine->getRepository(HRPeriods::class);
    $estimatedtime=0;

    $schedulesWorker=$schedulesWorkersRepository->workerSchedule($worker, $date->format('Y-m-d'));
    $schedulesWorker=$schedulesWorkersRepository->find($schedulesWorker);
    if(!$schedulesWorker) return -1;
    $shifts=$scheduleShiftsRepository->findBy(["schedule"=>$schedulesWorker->getSchedule(), "active"=>1, "deleted"=>0]);
    if(!$shifts) return -2;
    $shift=null;
    if(count($shifts)>1){
        //TODO: calculate what shift applies, temporaly use the first shift
        $shift=$shifts[0];

    }else{
      $shift=$shifts[0];
    }
    $periods=$schedulePeriodsRepository->datePeriod($shift, $date);
    if(count($periods)){

       foreach($periods as $key=>$value){
         $periods[$key]["time"]=date_timestamp_get(date_create_from_format('Y-m-d H:i:s',$date->format('Y-m-d').' '.$value['end']))-date_timestamp_get(date_create_from_format('Y-m-d H:i:s',$date->format('Y-m-d').' '.$value['start']));
         $estimatedtime+=$periods[$key]["time"];
       }
    }
    return $estimatedtime;
  }

  /*
  Return the working time for the worker in date
  */
  public function workingTimeDay($date,$worker,$doctrine){
    if(HelperClocks::isHoliday($date, $worker, $doctrine)) return 0;
    if(HelperClocks::isSickLeave($date, $worker, $doctrine)) return 0;
    $vacationTime=HelperClocks::isVacation($date, $worker, $doctrine);
    if($vacationTime===true) return 0;
      else if($vacationTime===false) $vacationTime=0;

    $estimatedtime=HelperClocks::estimatedTimeDay($date, $worker, $doctrine);
    $estimatedtime=$estimatedtime-($vacationTime*3600);
    return $estimatedtime;
  }



}

?>
