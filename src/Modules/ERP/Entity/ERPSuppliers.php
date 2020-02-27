<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\Globale\Entity\GlobaleActivities;
use \App\Modules\Globale\Entity\GlobaleCurrencies;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPPaymentMethods;
use \App\Modules\Globale\Entity\GlobaleStates;
use \App\Modules\ERP\Entity\ERPPaymentTerms;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSuppliersRepository")
 */
class ERPSuppliers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $vat;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $socialname;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=70)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $postcode;

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
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $web;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleActivities")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $activity;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCurrencies")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     *
     */
    private $taxexection;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentMethods")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $paymentmethod;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minorder;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $freeshipping;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $estimateddelivery;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $averagedelivery;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cancelremains;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $dropshipping;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $allowpicking;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $creditor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoiceday;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleStates")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentTerms")
     */
    private $paymentterms;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSocialname(): ?string
    {
        return $this->socialname;
    }

    public function setSocialname(string $socialname): self
    {
        $this->socialname = $socialname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?GlobaleCountries
    {
        return $this->country;
    }

    public function setCountry(?GlobaleCountries $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function setWeb(?string $web): self
    {
        $this->web = $web;

        return $this;
    }

    public function getActivity(): ?GlobaleActivities
    {
        return $this->activity;
    }

    public function setActivity(?GlobaleActivities $activity): self
    {
        $this->activity = $activity;

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

    public function getTaxexection(): ?string
    {
        return $this->taxexection;
    }

    public function setTaxexection(?string $taxexection): self
    {
        $this->taxexection = $taxexection;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

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

    public function getPaymentmethod(): ?ERPPaymentMethods
    {
        return $this->paymentmethod;
    }

    public function setPaymentmethod(?ERPPaymentMethods $paymentmethod): self
    {
        $this->paymentmethod = $paymentmethod;

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

    public function getEstimateddelivery(): ?int
    {
        return $this->estimateddelivery;
    }

    public function setEstimateddelivery(?int $estimateddelivery): self
    {
        $this->estimateddelivery = $estimateddelivery;

        return $this;
    }

    public function getAveragedelivery(): ?int
    {
        return $this->averagedelivery;
    }

    public function setAveragedelivery(?int $averagedelivery): self
    {
        $this->averagedelivery = $averagedelivery;

        return $this;
    }

    public function getCancelremains(): ?bool
    {
        return $this->cancelremains;
    }

    public function setCancelremains(?bool $cancelremains): self
    {
        $this->cancelremains = $cancelremains;

        return $this;
    }

    public function getDropshipping(): ?bool
    {
        return $this->dropshipping;
    }

    public function setDropshipping(?bool $dropshipping): self
    {
        $this->dropshipping = $dropshipping;

        return $this;
    }

    public function getAllowpicking(): ?bool
    {
        return $this->allowpicking;
    }

    public function setAllowpicking(?bool $allowpicking): self
    {
        $this->allowpicking = $allowpicking;

        return $this;
    }

    public function getCreditor(): ?bool
    {
        return $this->creditor;
    }

    public function setCreditor(?bool $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getInvoiceday(): ?int
    {
        return $this->invoiceday;
    }

    public function setInvoiceday(?int $invoiceday): self
    {
        $this->invoiceday = $invoiceday;

        return $this;
    }

    public function getState(): ?GlobaleStates
    {
        return $this->state;
    }

    public function setState(?GlobaleStates $state): self
    {
        $this->state = $state;

        return $this;
    }


    public function formValidation($kernel, $doctrine, $user, $validationParams){
      $fieldErrors=[];
      if($this->country==null){
        $fieldErrors["country"]="This field is required.";
      }
      if($this->state==null){
        $fieldErrors["state"]="This field is required.";
      }
      if($this->currency==null){
        $fieldErrors["currency"]="This field is required.";
      }
      if($this->paymentmethod==null){
        $fieldErrors["paymentmethod"]="This field is required.";
      }

      if (empty($fieldErrors)) return ["valid"=>true];
        else return ["valid"=>false, "field_errors"=>$fieldErrors];
    }

    public function getPaymentterms(): ?ERPPaymentTerms
    {
        return $this->paymentterms;
    }

    public function setPaymentterms(?ERPPaymentTerms $paymentterms): self
    {
        $this->paymentterms = $paymentterms;

        return $this;
    }
}
