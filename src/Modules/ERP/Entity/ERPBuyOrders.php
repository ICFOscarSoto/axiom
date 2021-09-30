<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPPaymentMethods;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\ERP\Entity\ERPBuyOrdersStates;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\ERP\Entity\ERPBuyOrdersRepository")
 */
class ERPBuyOrders
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
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $suppliername;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $suppliercode;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $phone;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentMethods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $paymentmethod;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $priority;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minorder;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $freeshipping;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $additionalcost;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $weight;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     */
    private $store;

    /**
     * @ORM\Column(type="datetime")
     */
    private $estimateddelivery;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $shippingcharge;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $shippingcosts;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPBuyOrdersStates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getSuppliername(): ?string
    {
        return $this->suppliername;
    }

    public function setSuppliername(string $suppliername): self
    {
        $this->suppliername = $suppliername;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPaymentmethod(): ?ERPPaymentMethods
    {
        return $this->paymentmethod;
    }

    public function setPaymentmethod(?ERPPaymentMethods $paymentmethod): self
    {
        $this->paymentmethod = $paymentmethod;

        return $this;
    }

    public function getPriority(): ?bool
    {
        return $this->priority;
    }

    public function setPriority(?bool $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getMinorder(): ?float
    {
        return $this->minorder;
    }

    public function setMinorder(?float $minorder): self
    {
        $this->minorder = $minorder;

        return $this;
    }

    public function getFreeshipping(): ?float
    {
        return $this->freeshipping;
    }

    public function setFreeshipping(?float $freeshipping): self
    {
        $this->freeshipping = $freeshipping;

        return $this;
    }

    public function getAdditionalcost(): ?float
    {
        return $this->additionalcost;
    }

    public function setAdditionalcost(?float $additionalcost): self
    {
        $this->additionalcost = $additionalcost;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

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

    public function getStore(): ?ERPStores
    {
        return $this->store;
    }

    public function setStore(?ERPStores $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function getEstimateddelivery(): ?\DateTimeInterface
    {
        return $this->estimateddelivery;
    }

    public function setEstimateddelivery(\DateTimeInterface $estimateddelivery): self
    {
        $this->estimateddelivery = $estimateddelivery;

        return $this;
    }

    public function getShippingcharge(): ?int
    {
        return $this->shippingcharge;
    }

    public function setShippingcharge(?int $shippingcharge): self
    {
        $this->shippingcharge = $shippingcharge;

        return $this;
    }

    public function getShippingcosts(): ?float
    {
        return $this->shippingcosts;
    }

    public function setShippingcosts(?float $shippingcosts): self
    {
        $this->shippingcosts = $shippingcosts;

        return $this;
    }

    public function getState(): ?ERPBuyOrdersStates
    {
        return $this->state;
    }

    public function setState(?ERPBuyOrdersStates $state): self
    {
        $this->state = $state;

        return $this;
    }
}
