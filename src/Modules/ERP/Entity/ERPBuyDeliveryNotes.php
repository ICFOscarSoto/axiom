<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\ERP\Entity\ERPBuyDeliveryNotesStates;
use \App\Modules\ERP\Entity\ERPInputs;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPBuyDeliveryNotesRepository")
 */
class ERPBuyDeliveryNotes
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     */
    private $store;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPInputs")
     */
    private $input;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPBuyDeliveryNotesStates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $theircode;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $suppliervat;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $suppliercode;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $suppliername;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $supplieremail;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $supplierphone;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $base;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxes;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $shipping;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observation;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
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

    public function getSupplier(): ?ERPSuppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?ERPSuppliers $supplier): self
    {
        $this->supplier = $supplier;

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

    public function getInput(): ?ERPInput
    {
        return $this->input;
    }

    public function setInput(?ERPInput $input): self
    {
        $this->input = $input;

        return $this;
    }

    public function getState(): ?ERPBuyDeliveryNotesStates
    {
        return $this->state;
    }

    public function setState(?ERPBuyDeliveryNotesStates $state): self
    {
        $this->state = $state;

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

    public function getTheircode(): ?string
    {
        return $this->theircode;
    }

    public function setTheircode(string $theircode): self
    {
        $this->theircode = $theircode;

        return $this;
    }

    public function getSuppliervat(): ?string
    {
        return $this->suppliervat;
    }

    public function setSuppliervat(string $suppliervat): self
    {
        $this->suppliervat = $suppliervat;

        return $this;
    }

    public function getSuppliercode(): ?string
    {
        return $this->suppliercode;
    }

    public function setSuppliercode(string $suppliercode): self
    {
        $this->suppliercode = $suppliercode;

        return $this;
    }

    public function getSuppliername(): ?string
    {
        return $this->suppliername;
    }

    public function setSuppliername(string $suppliername): self
    {
        $this->suppliername = $suppliername;

        return $this;
    }

    public function getSupplieremail(): ?string
    {
        return $this->supplieremail;
    }

    public function setSupplieremail(string $supplieremail): self
    {
        $this->supplieremail = $supplieremail;

        return $this;
    }

    public function getSupplierphone(): ?string
    {
        return $this->supplierphone;
    }

    public function setSupplierphone(string $supplierphone): self
    {
        $this->supplierphone = $supplierphone;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getBase(): ?float
    {
        return $this->base;
    }

    public function setBase(?float $base): self
    {
        $this->base = $base;

        return $this;
    }

    public function getTaxes(): ?float
    {
        return $this->taxes;
    }

    public function setTaxes(?float $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    public function getShipping(): ?float
    {
        return $this->shipping;
    }

    public function setShipping(?float $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;

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
