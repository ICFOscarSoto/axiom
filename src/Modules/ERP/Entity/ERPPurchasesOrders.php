<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\Globale\Entity\GlobaleCurrencies;
use \App\Modules\ERP\Entity\ERPSeries;
use \App\Modules\ERP\Entity\ERPPurchasesBudgets;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\ERP\Entity\ERPPurchasesOrdersRepository")
 */
class ERPPurchasesOrders
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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $agent;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCurrencies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPFinancialYears")
     */
    private $financialyear;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentMethods")
     */
    private $paymentmethod;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSeries")
     */
    private $serie;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     */
    private $supplier;

    /**
     * @ORM\Column(type="string", length=14, nullable=true)
     */
    private $vat;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $suppliername;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $supplieraddress;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     */
    private $suppliercountry;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    private $suppliercity;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $supplierstate;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $supplierpostcode;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $supplierpostbox;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $suppliercode;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofferend;

    /**
     * @ORM\Column(type="boolean")
     */
    private $irpf=0;

    /**
     * @ORM\Column(type="float")
     */
    private $irpfperc=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $surcharge=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $taxexempt=0;

    /**
     * @ORM\Column(type="float")
     */
    private $totalnet=0;

    /**
     * @ORM\Column(type="float")
     */
    private $totaldto=0;

    /**
     * @ORM\Column(type="float")
     */
    private $totalbase=0;

    /**
     * @ORM\Column(type="float")
     */
    private $totaltax=0;

    /**
     * @ORM\Column(type="float")
     */
    private $totalsurcharge=0;

    /**
     * @ORM\Column(type="float")
     */
    private $totalirpf=0;

    /**
     * @ORM\Column(type="float")
     */
    private $total=0;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

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
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPurchasesBudgets")
     */
    private $purchasesbudget;


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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCurrency(): ?GlobaleCurrencies
    {
        return $this->currency;
    }

    public function setCurrency(?GlobaleCurrencies $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getFinancialyear(): ?ERPFinancialYears
    {
        return $this->financialyear;
    }

    public function setFinancialyear(?ERPFinancialYears $financialyear): self
    {
        $this->financialyear = $financialyear;

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

    public function getPaymentmethod(): ?ERPPaymentMethods
    {
        return $this->paymentmethod;
    }

    public function setPaymentmethod(?ERPPaymentMethods $paymentmethod): self
    {
        $this->paymentmethod = $paymentmethod;

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

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setVat(?string $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function getSuppliername(): ?string
    {
        return $this->suppliername;
    }

    public function setSuppliername(?string $suppliername): self
    {
        $this->suppliername = $suppliername;

        return $this;
    }

    public function getSupplieraddress(): ?string
    {
        return $this->supplieraddress;
    }

    public function setSupplieraddress(?string $supplieraddress): self
    {
        $this->supplieraddress = $supplieraddress;

        return $this;
    }

    public function getSuppliercountry(): ?GlobaleCountries
    {
        return $this->suppliercountry;
    }

    public function setSuppliercountry(?GlobaleCountries $suppliercountry): self
    {
        $this->suppliercountry = $suppliercountry;

        return $this;
    }

    public function getSuppliercity(): ?string
    {
        return $this->suppliercity;
    }

    public function setSuppliercity(?string $suppliercity): self
    {
        $this->suppliercity = $suppliercity;

        return $this;
    }

    public function getSupplierstate(): ?string
    {
        return $this->supplierstate;
    }

    public function setSupplierstate(?string $supplierstate): self
    {
        $this->supplierstate = $supplierstate;

        return $this;
    }

    public function getSupplierpostcode(): ?string
    {
        return $this->supplierpostcode;
    }

    public function setSupplierpostcode(?string $supplierpostcode): self
    {
        $this->supplierpostcode = $supplierpostcode;

        return $this;
    }

    public function getSupplierpostbox(): ?string
    {
        return $this->supplierpostbox;
    }

    public function setSupplierpostbox(?string $supplierpostbox): self
    {
        $this->supplierpostbox = $supplierpostbox;

        return $this;
    }

    public function getSuppliercode(): ?string
    {
        return $this->suppliercode;
    }

    public function setSuppliercode(?string $suppliercode): self
    {
        $this->suppliercode = $suppliercode;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDateofferend(): ?\DateTimeInterface
    {
        return $this->dateofferend;
    }

    public function setDateofferend(?\DateTimeInterface $dateofferend): self
    {
        $this->dateofferend = $dateofferend;

        return $this;
    }

    public function getIrpf(): ?bool
    {
        return $this->irpf;
    }

    public function setIrpf(bool $irpf): self
    {
        $this->irpf = $irpf;

        return $this;
    }

    public function getIrpfperc(): ?float
    {
        return $this->irpfperc;
    }

    public function setIrpfperc(float $irpfperc): self
    {
        $this->irpfperc = $irpfperc;

        return $this;
    }

    public function getSurcharge(): ?bool
    {
        return $this->surcharge;
    }

    public function setSurcharge(bool $surcharge): self
    {
        $this->surcharge = $surcharge;

        return $this;
    }

    public function getTaxexempt(): ?bool
    {
        return $this->taxexempt;
    }

    public function setTaxexempt(bool $taxexempt): self
    {
        $this->taxexempt = $taxexempt;

        return $this;
    }

    public function getTotalnet(): ?float
    {
        return $this->totalnet;
    }

    public function setTotalnet(float $totalnet): self
    {
        $this->totalnet = $totalnet;

        return $this;
    }

    public function getTotaldto(): ?float
    {
        return $this->totaldto;
    }

    public function setTotaldto(float $totaldto): self
    {
        $this->totaldto = $totaldto;

        return $this;
    }

    public function getTotalbase(): ?float
    {
        return $this->totalbase;
    }

    public function setTotalbase(float $totalbase): self
    {
        $this->totalbase = $totalbase;

        return $this;
    }

    public function getTotaltax(): ?float
    {
        return $this->totaltax;
    }

    public function setTotaltax(float $totaltax): self
    {
        $this->totaltax = $totaltax;

        return $this;
    }

    public function getTotalsurcharge(): ?float
    {
        return $this->totalsurcharge;
    }

    public function setTotalsurcharge(float $totalsurcharge): self
    {
        $this->totalsurcharge = $totalsurcharge;

        return $this;
    }

    public function getTotalirpf(): ?float
    {
        return $this->totalirpf;
    }

    public function setTotalirpf(float $totalirpf): self
    {
        $this->totalirpf = $totalirpf;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getPurchasesbudget(): ?ERPPurchasesBudgets
    {
        return $this->purchasesbudget;
    }

    public function setPurchasesbudget(?ERPPurchasesBudgets $purchasesbudget): self
    {
        $this->purchasesbudget = $purchasesbudget;

        return $this;
    }
    public function getSerie(): ?ERPSeries
    {
        return $this->serie;
    }

    public function setSerie(?ERPSeries $serie): self
    {
        $this->serie = $serie;

        return $this;
    }
}
