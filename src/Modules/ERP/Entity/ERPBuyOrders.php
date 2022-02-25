<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPPaymentMethods;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\ERP\Entity\ERPBuyOrdersStates;
use \App\Modules\Globale\Entity\GlobaleStates;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\ERP\Entity\ERPCustomers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPBuyOrdersRepository")
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

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $destinationaddress;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $destinationphone;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $destinationemail;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $destinationpostcode;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    private $destinationcity;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleStates")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $destinationstate;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     */
    private $destinationcountry;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $readed;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $supplierdeliverynote;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $destinationname;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $typeofconfirmation;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $supplieraddress;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $supplierpostcode;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $supplierstate;

    /**
     * @ORM\Column(type="string", length=70)
     */
    private $suppliercity;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $supplierVat;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $supplierpaymentterms;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $suppliercontact;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateread;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datesend;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateconfirmed;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $ouroffer;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $theiroffer;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxbase;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $additionaldiscount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxes;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $destinationcontact;





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

    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getDestinationaddress(): ?string
    {
        return $this->destinationaddress;
    }

    public function setDestinationaddress(?string $destinationaddress): self
    {
        $this->destinationaddress = $destinationaddress;

        return $this;
    }

    public function getDestinationphone(): ?string
    {
        return $this->destinationphone;
    }

    public function setDestinationphone(?string $destinationphone): self
    {
        $this->destinationphone = $destinationphone;

        return $this;
    }

    public function getDestinationemail(): ?string
    {
        return $this->destinationemail;
    }

    public function setDestinationemail(?string $destinationemail): self
    {
        $this->destinationemail = $destinationemail;

        return $this;
    }

    public function getDestinationpostcode(): ?string
    {
        return $this->destinationpostcode;
    }

    public function setDestinationpostcode(?string $destinationpostcode): self
    {
        $this->destinationpostcode = $destinationpostcode;

        return $this;
    }

    public function getDestinationcity(): ?string
    {
        return $this->destinationcity;
    }

    public function setDestinationcity(?string $destinationcity): self
    {
        $this->destinationcity = $destinationcity;

        return $this;
    }

    public function getDestinationstate(): ?GlobaleStates
    {
        return $this->destinationstate;
    }

    public function setDestinationstate(?GlobaleStates $destinationstate): self
    {
        $this->destinationstate = $destinationstate;

        return $this;
    }


    public function getDestinationcountry(): ?GlobaleCountries
    {
        return $this->destinationcountry;
    }

    public function setDestinationcountry(?GlobaleCountries $destinationcountry): self
    {
        $this->destinationcountry = $destinationcountry;

        return $this;
    }

    public function getReaded(): ?int
    {
        return $this->readed;
    }

    public function setReaded(?int $readed): self
    {
        $this->readed = $readed;

        return $this;
    }

    public function getSupplierdeliverynote(): ?string
    {
        return $this->supplierdeliverynote;
    }

    public function setSupplierdeliverynote(?string $supplierdeliverynote): self
    {
        $this->supplierdeliverynote = $supplierdeliverynote;

        return $this;
    }

    public function getDestinationname(): ?string
    {
        return $this->destinationname;
    }

    public function setDestinationname(?string $destinationname): self
    {
        $this->destinationname = $destinationname;

        return $this;
    }

    public function getTypeofconfirmation(): ?int
    {
        return $this->typeofconfirmation;
    }

    public function setTypeofconfirmation(?int $typeofconfirmation): self
    {
        $this->typeofconfirmation = $typeofconfirmation;

        return $this;
    }

    public function getSupplieraddress(): ?string
    {
        return $this->supplieraddress;
    }

    public function setSupplieraddress(string $supplieraddress): self
    {
        $this->supplieraddress = $supplieraddress;

        return $this;
    }

    public function getSupplierpostcode(): ?string
    {
        return $this->supplierpostcode;
    }

    public function setSupplierpostcode(string $supplierpostcode): self
    {
        $this->supplierpostcode = $supplierpostcode;

        return $this;
    }

    public function getSupplierstate(): ?string
    {
        return $this->supplierstate;
    }

    public function setSupplierstate(string $supplierstate): self
    {
        $this->supplierstate = $supplierstate;

        return $this;
    }

    public function getSuppliercity(): ?string
    {
        return $this->suppliercity;
    }

    public function setSuppliercity(string $suppliercity): self
    {
        $this->suppliercity = $suppliercity;

        return $this;
    }

    public function getSupplierVat(): ?string
    {
        return $this->supplierVat;
    }

    public function setSupplierVat(string $supplierVat): self
    {
        $this->supplierVat = $supplierVat;

        return $this;
    }

    public function getSupplierpaymentterms(): ?string
    {
        return $this->supplierpaymentterms;
    }

    public function setSupplierpaymentterms(string $supplierpaymentterms): self
    {
        $this->supplierpaymentterms = $supplierpaymentterms;

        return $this;
    }

    public function getSuppliercontact(): ?string
    {
        return $this->suppliercontact;
    }

    public function setSuppliercontact(?string $suppliercontact): self
    {
        $this->suppliercontact = $suppliercontact;

        return $this;
    }

    public function getDateread(): ?\DateTimeInterface
    {
        return $this->dateread;
    }

    public function setDateread(?\DateTimeInterface $dateread): self
    {
        $this->dateread = $dateread;

        return $this;
    }

    public function getDatesend(): ?\DateTimeInterface
    {
        return $this->datesend;
    }

    public function setDatesend(?\DateTimeInterface $datesend): self
    {
        $this->datesend = $datesend;

        return $this;
    }

    public function getDateconfirmed(): ?\DateTimeInterface
    {
        return $this->dateconfirmed;
    }

    public function setDateconfirmed(?\DateTimeInterface $dateconfirmed): self
    {
        $this->dateconfirmed = $dateconfirmed;

        return $this;
    }

    public function getOuroffer(): ?string
    {
        return $this->ouroffer;
    }

    public function setOuroffer(?string $ouroffer): self
    {
        $this->ouroffer = $ouroffer;

        return $this;
    }

    public function getTheiroffer(): ?string
    {
        return $this->theiroffer;
    }

    public function setTheiroffer(?string $theiroffer): self
    {
        $this->theiroffer = $theiroffer;

        return $this;
    }

    public function getTaxbase(): ?float
    {
        return $this->taxbase;
    }

    public function setTaxbase(?float $taxbase): self
    {
        $this->taxbase = $taxbase;

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

    public function getAdditionaldiscount(): ?float
    {
        return $this->additionaldiscount;
    }

    public function setAdditionaldiscount(?float $additionaldiscount): self
    {
        $this->additionaldiscount = $additionaldiscount;

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

    public function getDestinationcontact(): ?string
    {
        return $this->destinationcontact;
    }

    public function setDestinationcontact(?string $destinationcontact): self
    {
        $this->destinationcontact = $destinationcontact;

        return $this;
    }



}
