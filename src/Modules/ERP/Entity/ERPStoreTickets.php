<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPStoreTicketsReasons;
use \App\Modules\ERP\Entity\ERPStoreTicketsStates;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPVariantsValues;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\ERP\Entity\ERPStoreLocations;
use \App\Modules\HR\Entity\HRDepartments;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPSalesOrders;
use \App\Modules\ERP\Entity\ERPSalesTickets;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStoreTicketsRepository")
 */
class ERPStoreTickets
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
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreTicketsReasons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreTicketsStates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $storeticketstate;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPVariantsValues")
     */
    private $variant;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     */
    private $store;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreLocations", inversedBy="observations")
     */
    private $storelocation;

    /**
     * @ORM\Column(type="text")
     */
    private $observations;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRDepartments")
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

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
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datelastnotify;


    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSalesTickets", cascade={"persist", "remove"})
     */
    private $salesticket;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?GlobaleUsers
    {
        return $this->author;
    }

    public function setAuthor(?GlobaleUsers $author): self
    {
        $this->author = $author;

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

    public function getReason(): ?ERPStoreTicketsReasons
    {
        return $this->reason;
    }

    public function setReason(?ERPStoreTicketsReasons $reason): self
    {
        $this->reason = $reason;

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

    public function getProduct(): ?ERPProducts
    {
        return $this->product;
    }

    public function setProduct(?ERPProducts $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getVariant(): ?ERPVariantsValues
    {
        return $this->variant;
    }

    public function setVariant(?ERPVariantsValues $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getStore(): ?ERPStores
    {
        return $this->store;
    }

    public function setStore(?ERPStores $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function getStorelocation(): ?ERPStoreLocations
    {
        return $this->storelocation;
    }

    public function setStorelocation(?ERPStoreLocations $storelocation): self
    {
        $this->storelocation = $storelocation;

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

    public function getDepartment(): ?HRDepartments
    {
        return $this->department;
    }

    public function setDepartment(?HRDepartments $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getDatelastnotify(): ?\DateTimeInterface
    {
        return $this->datelastnotify;
    }

    public function setDatelastnotify(?\DateTimeInterface $datelastnotify): self
    {
        $this->datelastnotify = $datelastnotify;

        return $this;
    }


    public function getSalesticket(): ?ERPSalesTickets
    {
        return $this->salesticket;
    }

    public function setSalesticket(?ERPSalesTickets $salesticket): self
    {
        $this->salesticket = $salesticket;

        return $this;
    }

}
