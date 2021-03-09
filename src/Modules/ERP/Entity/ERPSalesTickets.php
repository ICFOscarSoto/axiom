<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPSalesTicketsStates;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSalesTicketsRepository")
 */
class ERPSalesTickets
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

    /**
     * @ORM\Column(type="text")
     */
    private $observations;

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
    private $deleted;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSalesTicketsStates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $salesticketstate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgent(): ?GlobaleUsers
    {
        return $this->agent;
    }

    public function setAgent(?GlobaleUsers $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(string $observations): self
    {
        $this->observations = $observations;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getSalesticketstate(): ?ERPSalesTicketsStates
    {
        return $this->salesticketstate;
    }

    public function setSalesticketstate(?ERPSalesTicketsStates $salesticketstate): self
    {
        $this->salesticketstate = $salesticketstate;

        return $this;
    }
}
