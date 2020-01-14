<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleStates;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\ERP\Entity\ERPCustomerGroups;
use \App\Modules\ERP\Entity\ERPPaymentMethods;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPPaymentTerms;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPCustomersRepository")
 */
class ERPCustomers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleStates")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $state;

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
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $vat;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxcredit;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $blockcredit;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomerGroups")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $customergroup;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentMethods")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $paymentmethod;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $mininvoice;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $allowlinediscount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $requiredordernumber;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $additionaldiscount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $invoicefordeliverynote;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $pricesdeliverynote;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $partialshipping;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoiceday;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

        /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $surcharge;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $web;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentTerms")
     * @ORM\JoinColumn(nullable=true)
     */
    private $paymentterms;

    public function getId(): ?int
    {
        return $this->id;
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

    public function setSocialname(?string $socialname): self
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

    public function getState(): ?GlobaleStates
    {
        return $this->state;
    }

    public function setState(?GlobaleStates $state): self
    {
        $this->state = $state;

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

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setVat(?string $vat): self
    {
        $this->vat = $vat;

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

    public function getMaxcredit(): ?float
    {
        return $this->maxcredit;
    }

    public function setMaxcredit(?float $maxcredit): self
    {
        $this->maxcredit = $maxcredit;

        return $this;
    }

    public function getBlockcredit(): ?bool
    {
        return $this->blockcredit;
    }

    public function setBlockcredit(?bool $blockcredit): self
    {
        $this->blockcredit = $blockcredit;

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

    public function getPaymentmethod(): ?ERPPaymentMethods
    {
        return $this->paymentmethod;
    }

    public function setPaymentmethod(?ERPPaymentMethods $paymentmethod): self
    {
        $this->paymentmethod = $paymentmethod;

        return $this;
    }

    public function getMininvoice(): ?float
    {
        return $this->mininvoice;
    }

    public function setMininvoice(?float $mininvoice): self
    {
        $this->mininvoice = $mininvoice;

        return $this;
    }

    public function getAllowlinediscount(): ?bool
    {
        return $this->allowlinediscount;
    }

    public function setAllowlinediscount(?bool $allowlinediscount): self
    {
        $this->allowlinediscount = $allowlinediscount;

        return $this;
    }

    public function getRequiredordernumber(): ?bool
    {
        return $this->requiredordernumber;
    }

    public function setRequiredordernumber(?bool $requiredordernumber): self
    {
        $this->requiredordernumber = $requiredordernumber;

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

    public function getInvoicefordeliverynote(): ?bool
    {
        return $this->invoicefordeliverynote;
    }

    public function setInvoicefordeliverynote(?bool $invoicefordeliverynote): self
    {
        $this->invoicefordeliverynote = $invoicefordeliverynote;

        return $this;
    }

    public function getPricesdeliverynote(): ?bool
    {
        return $this->pricesdeliverynote;
    }

    public function setPricesdeliverynote(?bool $pricesdeliverynote): self
    {
        $this->pricesdeliverynote = $pricesdeliverynote;

        return $this;
    }

    public function getPartialshipping(): ?bool
    {
        return $this->partialshipping;
    }

    public function setPartialshipping(?bool $partialshipping): self
    {
        $this->partialshipping = $partialshipping;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getSurcharge(): ?bool
    {
        return $this->surcharge;
    }

    public function setSurcharge(?bool $surcharge): self
    {
        $this->surcharge = $surcharge;

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
      if($this->customergroup==null){
        $fieldErrors["customergroup"]="This field is required.";
      }
      if($this->paymentmethod==null){
        $fieldErrors["paymentmethod"]="This field is required.";
      }

      if (empty($fieldErrors)) return ["valid"=>true];
        else return ["valid"=>false, "field_errors"=>$fieldErrors];
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

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function setWeb(?string $web): self
    {
        $this->web = $web;

        return $this;
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
