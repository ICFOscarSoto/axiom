<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRWorkers;
use \App\Modules\HR\Entity\HRClocksDiary;
use \App\Modules\HR\Entity\HRSchedulesWorkers;
use \App\Modules\HR\Entity\HRSchedules;
use \App\Modules\HR\Entity\HRShifts;
use \App\Modules\HR\Entity\HRPeriods;
use \App\Modules\Globale\Entity\GlobaleClockDevices;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRClocksRepository")
 */
class HRClocks
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRWorkers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $worker;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateupd;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted=0;

    public $newSeconds=3600;
  	public $updatedSeconds=3600;

   /**
    * @ORM\Column(type="smallint", nullable=true)
    */
   private $invalid;

   /**
    * @ORM\Column(type="string", length=25, nullable=true)
    */
   private $startLatitude;

   /**
    * @ORM\Column(type="string", length=25, nullable=true)
    */
   private $startLongitude;

   /**
    * @ORM\Column(type="string", length=25, nullable=true)
    */
   private $endLatitude;

   /**
    * @ORM\Column(type="string", length=25, nullable=true)
    */
   private $endLongitude;

   /**
    * @ORM\Column(type="integer", nullable=true)
    */
   private $time;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleClockDevices")
    * @ORM\JoinColumn(onDelete="SET NULL")
    */
   private $startdevice;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleClockDevices")
    * @ORM\JoinColumn(onDelete="SET NULL")
    */
   private $enddevice;

   /**
    * @ORM\Column(type="string", length=150, nullable=true)
    */
   private $observations;

   /**
    * @ORM\Column(type="string", length=64, nullable=true)
    */
   private $startIPaddress;

   /**
    * @ORM\Column(type="string", length=64, nullable=true)
    */
   private $endIPaddress;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorker(): ?HRWorkers
    {
        return $this->worker;
    }

    public function setWorker(?HRWorkers $worker): self
    {
        $this->worker = $worker;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
      return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;
        //$this->time = $this->calculateTime();

        return $this;
    }

    public function getDateadd(): ?\DateTimeInterface
    {
        return $this->dateadd;
    }

    public function setDateadd(\DateTimeInterface $dateadd): self
    {
        $this->dateadd = $dateadd;

        return $this;
    }

    public function getDateupd(): ?\DateTimeInterface
    {
        return $this->dateupd;
    }

    public function setDateupd(\DateTimeInterface $dateupd): self
    {
        $this->dateupd = $dateupd;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getInvalid(): ?int
    {
        return $this->invalid;
    }

    public function setInvalid(?int $invalid): self
    {
        $this->invalid = $invalid;

        return $this;
    }

    public function getStartLatitude(): ?string
    {
        return $this->startLatitude;
    }

    public function setStartLatitude(?string $startLatitude): self
    {
        $this->startLatitude = $startLatitude;

        return $this;
    }

    public function getStartLongitude(): ?string
    {
        return $this->startLongitude;
    }

    public function setStartLongitude(?string $startLongitude): self
    {
        $this->startLongitude = $startLongitude;

        return $this;
    }

    public function getEndLatitude(): ?string
    {
        return $this->endLatitude;
    }

    public function setEndLatitude(?string $endLatitude): self
    {
        $this->endLatitude = $endLatitude;

        return $this;
    }

    public function getEndLongitude(): ?string
    {
        return $this->endLongitude;
    }

    public function setEndLongitude(?string $endLongitude): self
    {
        $this->endLongitude = $endLongitude;
        return $this;
    }

    public function getTime(): ?int{
        if ($this->time==null){
           return date_timestamp_get(new \DateTime())-date_timestamp_get(($this->start!=null)?$this->start: new \DateTime());
         }else return $this->time;
    }

    private function dateIntervalToSeconds($dateInterval){
      $reference = new \DateTimeImmutable;
      $endTime = $reference->add($dateInterval);
      return $endTime->getTimestamp() - $reference->getTimestamp();
    }

    public function setTime(int $time): self
    {
        $this->time = $time;
        return $this;
    }

    public function preProccess($kernel, $doctrine, $user){
      //$this->time = $this->calculateTime();
      if($this->end!=null) $this->time = date_timestamp_get($this->end)-date_timestamp_get($this->start);

    }
    public function postProccess($kernel, $doctrine, $user){
      $clocksrepository=$doctrine->getRepository(HRClocks::class);
      $clocksDiaryrepository=$doctrine->getRepository(HRClocksDiary::class);
      $schedulesWorkersRepository=$doctrine->getRepository(HRSchedulesWorkers::class);
      $schedulesRepository=$doctrine->getRepository(HRSchedules::class);
      $scheduleShiftsRepository=$doctrine->getRepository(HRShifts::class);
      $schedulePeriodsRepository=$doctrine->getRepository(HRPeriods::class);
      $vacationsRepository=$doctrine->getRepository(HRVacations::class);

      //Calculate total time worked in day
      $dayClocks=$clocksrepository->dayClocks($this->worker, $this->start->format('Y/m/d'));
      $time=0;
      foreach($dayClocks as $key=>$value){
        $time+=$value["time"];
      }
      //Get current worker estimated scheduled time
      $schedule=$schedulesWorkersRepository->workerSchedule($this->worker, $this->start->format('Y/m/d'));
      $schedule=$schedulesRepository->findOneBy(["id"=>$schedule, "company"=>$this->worker->getCompany(), "active"=>1, "deleted"=>0]);
      if(!$schedule) return;
      $shifts=$scheduleShiftsRepository->findBy(["schedule"=>$schedule, "active"=>1, "deleted"=>0]);
      if(!$shifts) return;
      $shift=null;
      if(count($shifts)>1){
          //TODO: calculate what shift applies

      }else{
        $shift=$shifts[0];
      }
      $periods=$schedulePeriodsRepository->datePeriod($shift, $this->start);
      $estimatedtime=0;
      foreach($periods as $key=>$value){
        if($this->end==null){
          $end=new \DateTime();
          $start=new \DateTime();
        }else{
          $end=$this->end;
          $start=$this->start;
        }
        $periods[$key]["time"]=date_timestamp_get(date_create_from_format('Y-m-d H:i:s',$end->format('Y-m-d').' '.$value['end']))-date_timestamp_get(date_create_from_format('Y-m-d H:i:s',$start->format('Y-m-d').' '.$value['start']));
        $estimatedtime+=$periods[$key]["time"];
      }

      $normalizeddate=date_create_from_format('Y-m-d H:i:s',$this->start->format('Y-m-d').' 00:00:00');
      $vacations=$vacationsRepository->dayVacations($this->worker, $normalizeddate->format('Y-m-d'));
      if($vacations){
        //check if is lastday of vacations
         $lastday=date_create_from_format('Y-m-d H:i:s',$vacations["end"]);
         if($vacations==$normalizeddate && $vacations["ourlastday"]!=0 && $vacations["ourlastday"]!=null){
             $estimatedtime=$estimatedtime-($vacations["ourlastday"]*3600);
         }
       }

      $clockdiary=$clocksDiaryrepository->findOneBy(['worker'=>$this->worker, 'date'=>$normalizeddate, 'active'=>1, 'deleted'=>0]);
      if(!$clockdiary){
        $clockdiary=new HRClocksDiary();
        $clockdiary->setWorker($this->worker);
        $clockdiary->setCompany($this->worker->getCompany());
        $clockdiary->setDate($normalizeddate);
        $clockdiary->setExcludedifftime(0);
        $clockdiary->setDateadd(new \DateTime());
        $clockdiary->setActive(1);
        $clockdiary->setDeleted(0);
      }
      $clockdiary->setTime($time);
      $clockdiary->setEstimatedtime($estimatedtime);
      $clockdiary->setDifftime($time-$estimatedtime);
      $clockdiary->setDateupd(new \DateTime());
      $doctrine->getManager()->persist($clockdiary);
      $doctrine->getManager()->flush();
    }

    public function getStartdevice(): ?GlobaleClockDevices
    {
        return $this->startdevice;
    }

    public function setStartdevice(?GlobaleClockDevices $startdevice): self
    {
        $this->startdevice = $startdevice;

        return $this;
    }

    public function getEnddevice(): ?GlobaleClockDevices
    {
        return $this->enddevice;
    }

    public function setEnddevice(?GlobaleClockDevices $enddevice): self
    {
        $this->enddevice = $enddevice;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

        return $this;
    }

    public function getStartIPaddress(): ?string
    {
        return $this->startIPaddress;
    }

    public function setStartIPaddress(?string $startIPaddress): self
    {
        $this->startIPaddress = $startIPaddress;

        return $this;
    }

    public function getEndIPaddress(): ?string
    {
        return $this->endIPaddress;
    }

    public function setEndIPaddress(?string $endIPaddress): self
    {
        $this->endIPaddress = $endIPaddress;

        return $this;
    }

}
