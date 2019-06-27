<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleClockDevices;
use \App\Modules\HR\Entity\HRWorkers;
use \App\Modules\Globale\Utils\GlobaleClockDevicesUtils;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleClockDevicesWorkersRepository")
 */
class GlobaleClockDevicesWorkers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleClockDevices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $clockdevice;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRWorkers")
     */
    private $worker;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idd;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClockdevice(): ?GlobaleClockDevices
    {
        return $this->clockdevice;
    }

    public function setClockdevice(?GlobaleClockDevices $clockdevice): self
    {
        $this->clockdevice = $clockdevice;

        return $this;
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

    public function getIdd(): ?int
    {
        return $this->idd;
    }

    public function setIdd(?int $idd): self
    {
        $this->idd = $idd;

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

    public function postProccess($kernel, $doctrine, $user){
      if($this->getActive() && !$this->getDeleted()){
        $utils = new GlobaleClockDevicesUtils();

        $params=["id"=>$this->getClockdevice()->getIdentifier(),
                 "idd"=>$this->getIdd(),
                 "worker"=>$this->getWorker()
        ];
        $utils->setuserdata($params);
      }
    }
}
