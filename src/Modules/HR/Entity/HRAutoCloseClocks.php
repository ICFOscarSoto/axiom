<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRWorkCenters;
use \App\Modules\HR\Entity\HRDepartments;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRAutoCloseClocksRepository")
 */
class HRAutoCloseClocks
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRWorkCenters")
     */
    private $workcenter;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRDepartments")
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $time;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $companyown;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getWorkcenter(): ?HRWorkCenters
    {
        return $this->workcenter;
    }

    public function setWorkcenter(?HRWorkCenters $workcenter): self
    {
        $this->workcenter = $workcenter;

        return $this;
    }

    public function getDepartment(): ?HRDepartments
    {
        return $this->department;
    }

    public function setDepartment(?HRDepartments $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(?string $time): self
    {
        $this->time = $time;

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

    public function getCompanyown(): ?GlobaleCompanies
    {
        return $this->companyown;
    }

    public function setCompanyown(?GlobaleCompanies $companyown): self
    {
        $this->companyown = $companyown;

        return $this;
    }
}
