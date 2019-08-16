<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRLaborAgreementsRepository")
 */
class HRLaborAgreements
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $annualhours;

    /**
     * @ORM\Column(type="smallint")
     */
    private $weekhours;

    /**
     * @ORM\Column(type="smallint")
     */
    private $weekresthours;

    /**
     * @ORM\Column(type="smallint")
     */
    private $vacationdays;

    /**
     * @ORM\Column(type="smallint")
     */
    private $vacationtype;

    /**
     * @ORM\Column(type="smallint")
     */
    private $personalaffairdays;

    /**
     * @ORM\Column(type="smallint")
     */
    private $maxextrahours;

    /**
     * @ORM\Column(type="float")
     */
    private $kmprice;

    /**
     * @ORM\Column(type="float")
     */
    private $dietprice;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAnnualhours(): ?int
    {
        return $this->annualhours;
    }

    public function setAnnualhours(?int $annualhours): self
    {
        $this->annualhours = $annualhours;

        return $this;
    }

    public function getWeekhours(): ?int
    {
        return $this->weekhours;
    }

    public function setWeekhours(int $weekhours): self
    {
        $this->weekhours = $weekhours;

        return $this;
    }

    public function getWeekresthours(): ?int
    {
        return $this->weekresthours;
    }

    public function setWeekresthours(int $weekresthours): self
    {
        $this->weekresthours = $weekresthours;

        return $this;
    }

    public function getVacationdays(): ?int
    {
        return $this->vacationdays;
    }

    public function setVacationdays(int $vacationdays): self
    {
        $this->vacationdays = $vacationdays;

        return $this;
    }

    public function getVacationtype(): ?int
    {
        return $this->vacationtype;
    }

    public function setVacationtype(int $vacationtype): self
    {
        $this->vacationtype = $vacationtype;

        return $this;
    }

    public function getPersonalaffairdays(): ?int
    {
        return $this->personalaffairdays;
    }

    public function setPersonalaffairdays(int $personalaffairdays): self
    {
        $this->personalaffairdays = $personalaffairdays;

        return $this;
    }

    public function getMaxextrahours(): ?int
    {
        return $this->maxextrahours;
    }

    public function setMaxextrahours(int $maxextrahours): self
    {
        $this->maxextrahours = $maxextrahours;

        return $this;
    }

    public function getKmprice(): ?float
    {
        return $this->kmprice;
    }

    public function setKmprice(float $kmprice): self
    {
        $this->kmprice = $kmprice;

        return $this;
    }

    public function getDietprice(): ?float
    {
        return $this->dietprice;
    }

    public function setDietprice(float $dietprice): self
    {
        $this->dietprice = $dietprice;

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
