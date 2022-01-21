<?php

namespace App\Modules\HR\Entity;

use App\Modules\Globale\Entity\GlobaleCompanies;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRClocksDiaryRepository")
 */
class HRClocksDiary
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRWorkers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $worker;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="integer")
     */
    private $time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $estimatedtime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $difftime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $excludedifftime;

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

    public function getWorker(): ?HRWorkers
    {
        return $this->worker;
    }

    public function setWorker(?HRWorkers $worker): self
    {
        $this->worker = $worker;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getEstimatedtime(): ?int
    {
        return $this->estimatedtime;
    }

    public function setEstimatedtime(?int $estimatedtime): self
    {
        $this->estimatedtime = $estimatedtime;

        return $this;
    }

    public function getDifftime(): ?int
    {
        return $this->difftime;
    }

    public function setDifftime(?int $difftime): self
    {
        $this->difftime = $difftime;

        return $this;
    }

    public function getExcludedifftime(): ?bool
    {
        return $this->excludedifftime;
    }

    public function setExcludedifftime(bool $excludedifftime): self
    {
        $this->excludedifftime = $excludedifftime;

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
}
