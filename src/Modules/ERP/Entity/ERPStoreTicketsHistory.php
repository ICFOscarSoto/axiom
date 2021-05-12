<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPStoreTickets;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\HR\Entity\HRDepartments;
use \App\Modules\ERP\Entity\ERPStoreTicketsStates;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStoreTicketsHistoryRepository")
 */
class ERPStoreTicketsHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreTickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $storeticket;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $newagent;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRDepartments")
     */
    private $newdepartment;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreTicketsStates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $storeticketstate;

    /**
     * @ORM\Column(type="text")
     */
    private $observations;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStoreticket(): ?ERPStoreTickets
    {
        return $this->storeticket;
    }

    public function setStoreticket(?ERPStoreTickets $storeticket): self
    {
        $this->storeticket = $storeticket;

        return $this;
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

    public function getNewagent(): ?GlobaleUsers
    {
        return $this->newagent;
    }

    public function setNewagent(?GlobaleUsers $newagent): self
    {
        $this->newagent = $newagent;

        return $this;
    }


    public function getNewdepartment(): ?HRDepartments
    {
        return $this->newdepartment;
    }

    public function setNewdepartment(?HRDepartments $newdepartment): self
    {
        $this->newdepartment = $newdepartment;

        return $this;
    }

    public function getStoreticketstate(): ?ERPStoreTicketsStates
    {
        return $this->storeticketstate;
    }

    public function setStoreticketstate(?ERPStoreTicketsStates $storeticketstate): self
    {
        $this->storeticketstate = $storeticketstate;

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
}
