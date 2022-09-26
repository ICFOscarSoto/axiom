<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\Globale\Entity\Globaleusers;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPCarriers;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\ERP\Entity\ERPStoreLocations;
use \App\Modules\ERP\Entity\ERPSATWarrantiesStates;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\ERP\Entity\ERPVariants;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSATWarrantiesRepository")
 */
class ERPSATWarranties
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $repairnumber;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\Globaleusers")
     */
    private $salesmanager;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCarriers")
     */
    private $carrier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     */
    private $store;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreLocations")
     */
    private $storelocation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resolutiondate;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSATWarrantiesStates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPVariants")
     */
    private $variant;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepairnumber(): ?string
    {
        return $this->repairnumber;
    }

    public function setRepairnumber(string $repairnumber): self
    {
        $this->repairnumber = $repairnumber;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSalesmanager(): ?Globaleusers
    {
        return $this->salesmanager;
    }

    public function setSalesmanager(?Globaleusers $salesmanager): self
    {
        $this->salesmanager = $salesmanager;

        return $this;
    }

    public function getSupplier(): ?ERPSuppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?ERPSuppliers $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getCarrier(): ?ERPCarriers
    {
        return $this->carrier;
    }

    public function setCarrier(?ERPCarriers $carrier): self
    {
        $this->carrier = $carrier;

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

    public function getResolutiondate(): ?\DateTimeInterface
    {
        return $this->resolutiondate;
    }

    public function setResolutiondate(?\DateTimeInterface $resolutiondate): self
    {
        $this->resolutiondate = $resolutiondate;

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

    public function getState(): ?ERPSATWarrantiesStates
    {
        return $this->state;
    }

    public function setState(?ERPSATWarrantiesStates $state): self
    {
        $this->state = $state;

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

    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getVariant(): ?ERPVariants
    {
        return $this->variant;
    }

    public function setVariant(?ERPVariants $variant): self
    {
        $this->variant = $variant;

        return $this;
    }


}
