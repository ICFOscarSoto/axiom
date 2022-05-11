<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRWorkers;
use \App\Modules\HR\Helpers\HelperClocks;
use \App\Modules\HR\Entity\HRClocksDiary;


/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRVacationsRepository")
 */
class HRVacations
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
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $approved;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $workerobservations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $companyobservations;

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
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $days;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $requestdate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $hourslastday=0;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function setEnd(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getApproved(): ?int
    {
        return $this->approved;
    }

    public function setApproved(?int $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getWorkerobservations(): ?string
    {
        return $this->workerobservations;
    }

    public function setWorkerobservations(?string $workerobservations): self
    {
        $this->workerobservations = $workerobservations;

        return $this;
    }

    public function getCompanyobservations(): ?string
    {
        return $this->companyobservations;
    }

    public function setCompanyobservations(?string $companyobservations): self
    {
        $this->companyobservations = $companyobservations;

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

    public function getDays(): ?float
    {
      if ($this->days===null){
        $today=new \DateTime();
        return ($today->add(new \DateInterval('PT24H')))->diff($this->start)->format('%a');
       }else return $this->days;
    }

    public function setDays(?float $days): self
    {
        $this->days = $days;
        return $this;
    }

    public function getRequestdate(): ?\DateTimeInterface
    {
        return $this->requestdate;
    }

    public function setRequestdate(?\DateTimeInterface $requestdate): self
    {
        $this->requestdate = $requestdate;
        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $validationParams){
      if($this->worker->getSchedule()==null && $this->hourslastday>0)
        return ["valid"=>false, "global_errors"=>["Para definir una jornada parcial el trabajador debe tener un horario asociado."]];
      else if ($this->hourslastday>0 && $this->end!=$this->start)
        return ["valid"=>false, "global_errors"=>["Para definir una jornada parcial la fecha de inicio y final debe ser la misma"]];
          else return ["valid"=>true];
    }

    public function preProccess($kernel, $doctrine, $user, $preParams, $obj_old){
      $clocksDiaryrepository=$doctrine->getRepository(HRClocksDiary::class);

      //Check old range if exist for redo clocks diary
      if($obj_old && ($this->start!=$obj_old->getStart() || $this->end!=$obj_old->getEnd())) {

      }
      //Check new range
      //Check if there are clocks diaries between start and end if approved == true
      if($this->approved){
        $date     =clone $this->start;
        $date_end =clone $this->end;
        $date_end->modify('+1 day');
        $countDays=0;
        do{
          //Check every day if must compute for Vacations
          $estimatedtime=HelperClocks::estimatedTimeDay($date, $this->worker, $doctrine);
          if(!HelperClocks::isHoliday($date, $this->worker, $doctrine) && !HelperClocks::isSickLeave($date, $this->worker, $doctrine) && $estimatedtime>0){
            if($this->hourslastday!=0){
              $countDays=$countDays+round($this->hourslastday/(HelperClocks::estimatedTimeDay($date, $this->worker, $doctrine)/3600),2);
            }else $countDays++;
            //check if exist date in ClocksDiary
            $clockDiary=$clocksDiaryrepository->findOneBy(["date"=>$date, "worker"=>$this->worker, "active"=>1, "deleted"=>0]);
            if($clockDiary){
              $clockDiary->setEstimatedtime($estimatedtime);
              $clockDiary->setDifftime($clockdiary->getTime()-$estimatedtime);
              $clockDiary->setDateupd(new \DateTime());
              $doctrine->getManager()->persist($clockDiary);
              $doctrine->getManager()->flush();
            }
          }
          $date->modify('+1 day');
        }while($date!=$date_end);
        $this->days=$countDays;
      }
    }

    public function postProccess($kernel, $doctrine, $user){

    }

    public function getHourslastday(): ?float
    {
        return $this->hourslastday;
    }

    public function setHourslastday(?float $hourslastday): self
    {
        $this->hourslastday = $hourslastday;

        return $this;
    }
}
