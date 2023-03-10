<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\AERP\Entity\AERPCustomerGroups;
use \App\Helpers\HelperValidators;
use \App\Modules\AERP\Entity\AERPPaymentMethods;
/**
 * @ORM\Entity(repositoryClass="App\Modules\AERP\Repository\AERPCustomersRepository")
 */
class AERPCustomers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agentassign;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $vat;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $postbox;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPCustomerContacts")
     */
    private $shippcontact;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPCustomerGroups")
     */
    private $customergroup;

    /**
     * @ORM\Column(type="float")
     */
    private $maxrisk=0;

    /**
     * @ORM\Column(type="float")
     */
    private $risk=0;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="boolean")
     */
    private $taxexempt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $surcharge;

    /**
     * @ORM\Column(type="string", length=175, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", length=175, nullable=true)
     */
    private $web;

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
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $iban;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $swift;

    /**
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    private $accountingaccount;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPPaymentMethods")
     */
    private $paymentmethod;


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

    public function getAgentassign(): ?GlobaleUsers
    {
        return $this->agentassign;
    }

    public function setAgentassign(?GlobaleUsers $agentassign): self
    {
        $this->agentassign = $agentassign;

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

    public function getShippcontact(): ?AERPCustomerContacts
    {
        return $this->shippcontact;
    }

    public function setShippcontact(?AERPCustomerContacts $shippcontact): self
    {
        $this->shippcontact = $shippcontact;

        return $this;
    }

    public function getMaxrisk(): ?float
    {
        return $this->maxrisk;
    }

    public function setMaxrisk(?float $maxrisk): self
    {
        $this->maxrisk = $maxrisk;

        return $this;
    }

    public function getRisk(): ?float
    {
        return $this->risk;
    }

    public function setRisk(float $risk): self
    {
        $this->risk = $risk;

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

    public function getAuthor(): ?GlobaleUsers
    {
        return $this->author;
    }

    public function setAuthor(?GlobaleUsers $author): self
    {
        $this->author = $author;

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

    public function getSurcharge(): ?bool
    {
        return $this->surcharge;
    }

    public function setSurcharge(bool $surcharge): self
    {
        $this->surcharge = $surcharge;

        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function setWeb(string $web): self
    {
        $this->web = $web;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getPostbox(): ?string
    {
        return $this->postbox;
    }

    public function setPostbox(?string $postbox): self
    {
        $this->postbox = $postbox;

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

    public function getCustomergroup(): ?AERPCustomerGroups
    {
        return $this->customergroup;
    }

    public function setCustomergroup(?AERPCustomerGroups $customergroup): self
    {
        $this->customergroup = $customergroup;

        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $validationParams){
      $repository=$doctrine->getRepository(AERPCustomers::class);
      $fieldErrors=[];
      $validator=new HelperValidators();
      $this->vat=preg_replace('/[^\w]/', '', $this->vat);
      $obj=$repository->findOneBy(["vat"=>$this->vat,"company"=>$user->getCompany(),"deleted"=>0]);
      if($this->id==null){
        if($this->code==null){
          //If accountingaccount is null and object is new, create the next accounting account
          $this->code=$repository->getNextCode($user->getCompany()->getId());
        }else{
          //Check if accountingaccount is unique
          $objCode=$repository->findOneBy(["code"=>$this->code,"company"=>$user->getCompany(),"deleted"=>0]);
          if($objCode!=null) {$fieldErrors=["code"=>"C??digo ya asignado a ".$objCode->getName()]; }
        }
        if($this->accountingaccount==null){
          //If accountingaccount is null and object is new, create the next accounting account
          $this->accountingaccount=$repository->getNextAccounting($user->getCompany()->getId());
        }else{
          //Check if accountingaccount is unique
          $objAccounting=$repository->findOneBy(["accountingaccount"=>$this->accountingaccount,"company"=>$user->getCompany(),"deleted"=>0]);
          if($objAccounting!=null) {$fieldErrors=["accountingaccount"=>"Cuenta contable ya asignada a ".$objAccounting->getName()]; }
        }
      }

      if($obj!=null && $obj->id!=$this->id)
        return ["valid"=>false, "global_errors"=>["El cliente ya existe"]];
      else {
        //if($this->vat!=null && !$validator->isValidIdNumber($this->vat)) {$fieldErrors=["vat"=>"CIF/NIF/NIE no v??lido"]; }
        if($this->email!=null && !$validator->isValidEmail($this->email)) {$fieldErrors=["email"=>"Email no v??lido"]; }
        if($this->web!=null && !$validator->isValidURL($this->web)) {$fieldErrors=["web"=>"URL no v??lida"]; }
        if($this->iban!=null && !$validator->isValidIban($this->iban)) {$fieldErrors=["iban"=>"IBAN no v??lida"]; }
        if($this->swift!=null && !$validator->isValidSwift($this->swift)) {$fieldErrors=["swift"=>"SWIFT no v??lido"]; }

        return ["valid"=>empty($fieldErrors), "field_errors"=>$fieldErrors];
      }
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getSwift(): ?string
    {
        return $this->swift;
    }

    public function setSwift(?string $swift): self
    {
        $this->swift = $swift;

        return $this;
    }

    public function getAccountingaccount(): ?string
    {
        return $this->accountingaccount;
    }

    public function setAccountingaccount(?string $accountingaccount): self
    {
        $this->accountingaccount = $accountingaccount;

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

}
