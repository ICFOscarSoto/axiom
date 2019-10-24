<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\AERP\Entity\AERPCustomers;
use \App\Modules\Globale\Entity\GlobaleCurrencies;
use \App\Modules\AERP\Entity\AERPFinancialYears;
use \App\Modules\AERP\Entity\AERPPaymentMethods;
use \App\Modules\AERP\Entity\AERPSeries;
use \App\Modules\Globale\Entity\GlobaleCountries;

/**
 * @ORM\Entity(repositoryClass="App\Modules\AERP\Repository\AERPSalesBudgetsRepository")
 */
class AERPSalesBudgets
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPFinancialYears")
     * @ORM\JoinColumn(nullable=false)
     */
    private $financialyear;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPPaymentMethods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $paymentmethod;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPSeries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $serie;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPCustomers")
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
    private $irpf;

    /**
     * @ORM\Column(type="boolean")
     */
    private $surcharge;

    /**
     * @ORM\Column(type="float")
     */
    private $totalnet;

    /**
     * @ORM\Column(type="float")
     */
    private $totaldto;

    /**
     * @ORM\Column(type="float")
     */
    private $totalbase;

    /**
     * @ORM\Column(type="float")
     */
    private $totaltax;

    /**
     * @ORM\Column(type="float")
     */
    private $totalsurcharge;

    /**
     * @ORM\Column(type="float")
     */
    private $totalirpf;

    /**
     * @ORM\Column(type="float")
     */
    private $total;

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

    public function getCustomer(): ?AERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?AERPCustomers $customer): self
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

    public function getFinancialyear(): ?AERPFinancialYears
    {
        return $this->financialyear;
    }

    public function setFinancialyear(?AERPFinancialYears $financialyear): self
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

    public function getPaymentmethod(): ?AERPPaymentMethods
    {
        return $this->paymentmethod;
    }

    public function setPaymentmethod(?AERPPaymentMethods $paymentmethod): self
    {
        $this->paymentmethod = $paymentmethod;

        return $this;
    }

    public function getSerie(): ?AERPSeries
    {
        return $this->serie;
    }

    public function setSerie(?AERPSeries $serie): self
    {
        $this->serie = $serie;

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
}
