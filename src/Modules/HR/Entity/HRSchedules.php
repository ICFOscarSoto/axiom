<?php

namespace App\Modules\HR\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRShifts;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRSchedulesRepository")
 */
class HRSchedules
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $period;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    public function __construct()
    {
        $this->shifts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(int $period): self
    {
        $this->period = $period;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

}
