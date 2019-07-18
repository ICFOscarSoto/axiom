<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRWorkers;

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
     * @ORM\JoinColumn(nullable=false)
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $days;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $requestdate;

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

    public function getDays(): ?int
    {
      if ($this->days==null){
        $today=new \DateTime();
        return ($today->add(new \DateInterval('PT24H')))->diff($this->start)->format('%a');
       }else return $this->days;
    }

    public function setDays(?int $days): self
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

    public function preProccess($kernel, $doctrine, $user){
      $date_end=clone $this->getEnd();
      $date_start=clone $this->getStart();
      if($this->end!=null) $this->days = ($date_end->add(new \DateInterval('PT24H')))->diff($date_start)->format('%a');
    }
}
