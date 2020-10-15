<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\Globale\Entity\GlobaleCurrencies;
use \App\Modules\ERP\Entity\ERPFinancialYears;
use \App\Modules\ERP\Entity\ERPPaymentMethods;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\ERP\Entity\ERPCustomerGroups;
use \App\Modules\ERP\Entity\ERPSalesOrders;
use \App\Modules\ERP\Entity\ERPSeries;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSalesBudgetsRepository")
 */
class ERPSalesBudgets
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPSeries")
     */
    private $serie;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomerGroups")
     */
    private $customergroup;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $vat;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $customername;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $customeraddress;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     */
    private $customercountry;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    private $customercity;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $customerstate;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $customerpostcode;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $customerpostbox;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $shiptoname;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $shiptoaddress;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     */
    private $shiptocountry;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    private $shiptocity;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $shiptostate;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $shiptopostcode;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $shiptopostbox;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $customercode;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofferend;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateemail;

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
     * @ORM\Column(type="float")
     */
    private $cost;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSalesOrders")
     */
    private $inSalesOrder;

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

    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

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

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setVat(string $vat): self
    {
        $this->vat = $vat;

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

    public function getCustomername(): ?string
    {
        return $this->customername;
    }

    public function setCustomername(?string $customername): self
    {
        $this->customername = $customername;

        return $this;
    }

    public function getCustomeraddress(): ?string
    {
        return $this->customeraddress;
    }

    public function setCustomeraddress(?string $customeraddress): self
    {
        $this->customeraddress = $customeraddress;

        return $this;
    }

    public function getCustomercountry(): ?GlobaleCountries
    {
        return $this->customercountry;
    }

    public function setCustomercountry(?GlobaleCountries $customercountry): self
    {
        $this->customercountry = $customercountry;

        return $this;
    }

    public function getCustomercity(): ?string
    {
        return $this->customercity;
    }

    public function setCustomercity(?string $customercity): self
    {
        $this->customercity = $customercity;

        return $this;
    }

    public function getCustomerstate(): ?string
    {
        return $this->customerstate;
    }

    public function setCustomerstate(?string $customerstate): self
    {
        $this->customerstate = $customerstate;

        return $this;
    }

    public function getCustomerpostcode(): ?string
    {
        return $this->customerpostcode;
    }

    public function setCustomerpostcode(?string $customerpostcode): self
    {
        $this->customerpostcode = $customerpostcode;

        return $this;
    }

    public function getCustomerpostbox(): ?string
    {
        return $this->customerpostbox;
    }

    public function setCustomerpostbox(?string $customerpostbox): self
    {
        $this->customerpostbox = $customerpostbox;

        return $this;
    }

    public function getShiptoname(): ?string
    {
        return $this->shiptoname;
    }

    public function setShiptoname(?string $shiptoname): self
    {
        $this->shiptoname = $shiptoname;

        return $this;
    }

    public function getShiptoaddress(): ?string
    {
        return $this->shiptoaddress;
    }

    public function setShiptoaddress(?string $shiptoaddress): self
    {
        $this->shiptoaddress = $shiptoaddress;

        return $this;
    }

    public function getShiptocountry(): ?GlobaleCountries
    {
        return $this->shiptocountry;
    }

    public function setShiptocountry(?GlobaleCountries $shiptocountry): self
    {
        $this->shiptocountry = $shiptocountry;

        return $this;
    }

    public function getShiptocity(): ?string
    {
        return $this->shiptocity;
    }

    public function setShiptocity(?string $shiptocity): self
    {
        $this->shiptocity = $shiptocity;

        return $this;
    }

    public function getShiptostate(): ?string
    {
        return $this->shiptostate;
    }

    public function setShiptostate(?string $shiptostate): self
    {
        $this->shiptostate = $shiptostate;

        return $this;
    }

    public function getShiptopostcode(): ?string
    {
        return $this->shiptopostcode;
    }

    public function setShiptopostcode(?string $shiptopostcode): self
    {
        $this->shiptopostcode = $shiptopostcode;

        return $this;
    }

    public function getShiptopostbox(): ?string
    {
        return $this->shiptopostbox;
    }

    public function setShiptopostbox(?string $shiptopostbox): self
    {
        $this->shiptopostbox = $shiptopostbox;

        return $this;
    }

    public function getCustomercode(): ?string
    {
        return $this->customercode;
    }

    public function setCustomercode(?string $customercode): self
    {
        $this->customercode = $customercode;

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

    public function getDateemail(): ?\DateTimeInterface
    {
        return $this->dateemail;
    }

    public function setDateemail(?\DateTimeInterface $dateemail): self
    {
        $this->dateemail = $dateemail;

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

    public function getSurcharge(): ?bool
    {
        return $this->surcharge;
    }

    public function setSurcharge(bool $surcharge): self
    {
        $this->surcharge = $surcharge;

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

    public function getTotaldto(): ?float
    {
        return $this->totaldto;
    }

    public function setTotaldto(float $totaldto): self
    {
        $this->totaldto = $totaldto;

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

    public function getCustomergroup(): ?ERPCustomerGroups
    {
        return $this->customergroup;
    }

    public function setCustomergroup(?ERPCustomerGroups $customergroup): self
    {
        $this->customergroup = $customergroup;

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

    public function getIrpfperc(): ?float
    {
        return $this->irpfperc;
    }

    public function setIrpfperc(float $irpfperc): self
    {
        $this->irpfperc = $irpfperc;

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

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getInSalesOrder(): ?ERPSalesOrders
    {
        return $this->inSalesOrder;
    }

    public function setInSalesOrder(?ERPSalesOrders $inSalesOrder): self
    {
        $this->inSalesOrder = $inSalesOrder;

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
