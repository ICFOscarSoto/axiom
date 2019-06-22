<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRWorkers;
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
     * @ORM\JoinColumn(nullable=false)
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
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    public $newSeconds=3600;
  	public $updatedSeconds=3600;

   /**
    * @ORM\Column(type="boolean", nullable=true)
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
    */
   private $startdevice;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleClockDevices")
    */
   private $enddevice;

   /**
    * @ORM\Column(type="string", length=150, nullable=true)
    */
   private $observations;

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

    public function getInvalid(): ?bool
    {
        return $this->invalid;
    }

    public function setInvalid(?bool $invalid): self
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

}
