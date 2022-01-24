<?php
namespace App\Modules\HR\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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

class ClocksDiary extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('HR:clocksDiary')
            ->setDescription('Generar diario de fichajes');
        ;
  }

  protected function recreateDiaryDay($worker, $date){

  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();

    $usersRepository = $doctrine->getRepository(GlobaleUsers::class);
    $clocksrepository=$doctrine->getRepository(HRClocks::class);
    $workersRepository = $doctrine->getRepository(HRWorkers::class);
    $clocksDiaryrepository=$doctrine->getRepository(HRClocksDiary::class);
    $hollidaysrepository=$doctrine->getRepository(HRHollidays::class);
    $workCalendarsrepository=$doctrine->getRepository(HRWorkCalendars::class);
    $workCalendarGrouprepository=$doctrine->getRepository(HRWorkCalendarGroups::class);
    $schedulesWorkersRepository=$doctrine->getRepository(HRSchedulesWorkers::class);
    $schedulesRepository=$doctrine->getRepository(HRSchedules::class);
    $scheduleShiftsRepository=$doctrine->getRepository(HRShifts::class);
    $schedulePeriodsRepository=$doctrine->getRepository(HRPeriods::class);
    $sickLeavesRepository=$doctrine->getRepository(HRSickleaves::class);
    $vacationsRepository=$doctrine->getRepository(HRVacations::class);


    $output->writeln([
            'Recreate Clocks Diary',
            '=====================',
            '',
    ]);
    $schedulesWorkers=$schedulesWorkersRepository->findBy(["active"=>1, "deleted"=>0]);
    foreach($schedulesWorkers as $schedulesWorker){
      $date=$schedulesWorker->getStartdate();
      $today=new \DateTime('tomorrow midnight');

      while($date!=$today){
        $output->writeln("  ---".$date->format('d/m/Y')."---");
        //Search if worker has clocks this day
        $dayClocks=$clocksrepository->dayClocks($schedulesWorker->getWorker(), $date->format('Y/m/d'));
        if(count($dayClocks)){
          //the worker worked this day create Diary record by entity postProccess
          $clock=$clocksrepository->find($dayClocks[0]["id"]);
          $clock->postProccess(null, $doctrine, null);
        }else{
          //check if the worker had to work this day
          //first check by schedule
          $shifts=$scheduleShiftsRepository->findBy(["schedule"=>$schedulesWorker->getSchedule(), "active"=>1, "deleted"=>0]);
          if(!$shifts) continue;
          $shift=null;
          if(count($shifts)>1){
              //TODO: calculate what shift applies
          }else{
            $shift=$shifts[0];
          }
          $periods=$schedulePeriodsRepository->datePeriod($shift, $date);
          if(count($periods)){
             $estimatedtime=0;
             foreach($periods as $key=>$value){
               $periods[$key]["time"]=date_timestamp_get(date_create_from_format('Y-m-d H:i:s',$date->format('Y-m-d').' '.$value['end']))-date_timestamp_get(date_create_from_format('Y-m-d H:i:s',$date->format('Y-m-d').' '.$value['start']));
               $estimatedtime+=$periods[$key]["time"];
             }
             //Is a working day
             $workingDay=true;
             //Check if is a holliday
             $workCalendar=$workCalendarsrepository->findOneBy(["workcalendargroup"=>$schedulesWorker->getWorker()->getWorkcalendargroup(),"year"=>$date->format('Y'), "active"=>1, "deleted"=>0]);
             if($workCalendar!=null){
               $holliday=$hollidaysrepository->findOneBy(["calendar"=>$workCalendar, "date"=>$date, "active"=>1, "deleted"=>0]);
               if($holliday!=null) $workingDay=false;
             }
             //Check if is a sickleave day
             $sickLeaves=$sickLeavesRepository->daySickleave($schedulesWorker->getWorker(), $date->format('Y-m-d'));
             if($sickLeaves) $workingDay=false;
             //Check if is Vacation day and is approved
             $vacations=$vacationsRepository->dayVacations($schedulesWorker->getWorker(), $date->format('Y-m-d'));
             if($vacations){
               //check if is lastday of vacations
                $lastday=date_create_from_format('Y-m-d H:i:s',$vacations["end"]);
                if($vacations==$date && $vacations["ourlastday"]!=0 && $vacations["ourlastday"]!=null){
                    $estimatedtime=$estimatedtime-($vacations["ourlastday"]*3600);
                }else $workingDay=false;
              }

             if($workingDay){
               $clockdiary=new HRClocksDiary();
               $clockdiary->setWorker($schedulesWorker->getWorker());
               $clockdiary->setCompany($schedulesWorker->getWorker()->getCompany());
               $clockdiary->setDate($date);
               $clockdiary->setExcludedifftime(0);
               $clockdiary->setDateadd(new \DateTime());
               $clockdiary->setActive(1);
               $clockdiary->setDeleted(0);
               $clockdiary->setTime(0);
               $clockdiary->setEstimatedtime($estimatedtime);
               $clockdiary->setDifftime(0-$estimatedtime);
               $clockdiary->setDateupd(new \DateTime());
               $doctrine->getManager()->persist($clockdiary);
               $doctrine->getManager()->flush();
             }
          }// No working day continue
        }
        $date->modify('+1 day');
      }


    }
  }
}
?>
