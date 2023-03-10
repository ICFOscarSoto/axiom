<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRShifts;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRPeriodsRepository")
 */
class HRPeriods
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $start;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $end;

    /**
     * @ORM\Column(type="boolean")
     */
    private $monday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tuesday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $wednesday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $thursday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $friday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $saturday;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sunday;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRShifts", inversedBy="periods")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $shift;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active=true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateupd;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $fromdate;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $todate;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function setStart(string $start): self
    {
        $this->start = $start;
        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(string $end): self
    {
        $this->end = $end;
        return $this;
    }

    public function getMonday(): ?bool
    {
        return $this->monday;
    }

    public function setMonday(bool $monday): self
    {
        $this->monday = $monday;
        return $this;
    }

    public function getTuesday(): ?bool
    {
        return $this->tuesday;
    }

    public function setTuesday(bool $tuesday): self
    {
        $this->tuesday = $tuesday;
        return $this;
    }

    public function getWednesday(): ?bool
    {
        return $this->wednesday;
    }

    public function setWednesday(bool $wednesday): self
    {
        $this->wednesday = $wednesday;
        return $this;
    }

    public function getThursday(): ?bool
    {
        return $this->thursday;
    }

    public function setThursday(bool $thursday): self
    {
        $this->thursday = $thursday;

        return $this;
    }

    public function getFriday(): ?bool
    {
        return $this->friday;
    }

    public function setFriday(bool $friday): self
    {
        $this->friday = $friday;
        return $this;
    }

    public function getSaturday(): ?bool
    {
        return $this->saturday;
    }

    public function setSaturday(bool $saturday): self
    {
        $this->saturday = $saturday;
        return $this;
    }

    public function getSunday(): ?bool
    {
        return $this->sunday;
    }

    public function setSunday(bool $sunday): self
    {
        $this->sunday = $sunday;
        return $this;
    }

    public function getShift(): ?HRShifts
    {
        return $this->shift;
    }

    public function setShift(?HRShifts $shift): self
    {
        $this->shift = $shift;
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

    public function getFromdate(): ?string
    {
        return $this->fromdate;
    }

    public function setFromdate(string $fromdate): self
    {
        $this->fromdate = $fromdate;

        return $this;
    }

    public function getTodate(): ?string
    {
        return $this->todate;
    }

    public function setTodate(string $todate): self
    {
        $this->todate = $todate;

        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $validationParams){
      //Check for overlapped periods
      $repository=$doctrine->getRepository(HRPeriods::class);
      //Get registers where fromdate>=this fromdate and todate<= this todate and start>=this start and end<= this and ((monday=1 and this monday=1) or (tuesday=1 and this tuesday=1) or ...)
      $periods=$repository->findByPeriod($this);
      if($periods!=null)
        return ["valid"=>false, "global_errors"=>["El periodo definido se solapa con otro periodo de este grupo", "Revise las fechas y horas definidas en este periodo"]];
      else return ["valid"=>true];
    }
}
