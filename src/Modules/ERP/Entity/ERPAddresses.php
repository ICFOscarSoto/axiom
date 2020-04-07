<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleStates;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\ERP\Entity\ERPContacts;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPAddressesRepository")
 */
class ERPAddresses
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
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $name;

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
     * @ORM\JoinColumn(nullable=true, onDelete="Cascade")
     */
    private $state;

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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     * @ORM\JoinColumn(nullable=true, onDelete="Cascade")
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(onDelete="Cascade")
     */
    private $supplier;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     * @ORM\JoinColumn(onDelete="Cascade")
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $navisioncontact;

    /**
     * @ORM\OneToOne(targetEntity="\App\Modules\ERP\Entity\ERPContacts", cascade={"persist", "remove"})
     */
    private $contact;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deliveryaddress;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $invoiceaddress;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

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

    public function getCountry(): ?GlobaleCountries
    {
        return $this->country;
    }

    public function setCountry(?GlobaleCountries $country): self
    {
        $this->country = $country;

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

/*
    public function preProccess($kernel, $doctrine, $user,$params){
      if($params["type"]=="supplier"){
        $this->supplier=$params["obj"];
      }

    }
*/
    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getNavisioncontact(): ?string
    {
        return $this->navisioncontact;
    }

    public function setNavisioncontact(?string $navisioncontact): self
    {
        $this->navisioncontact = $navisioncontact;

        return $this;
    }

    public function getContact(): ?ERPContacts
    {
        return $this->contact;
    }

    public function setContact(?ERPContacts $contact): self
    {
        $this->contact = $contact;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDeliveryaddress(): ?bool
    {
        return $this->deliveryaddress;
    }

    public function setDeliveryaddress(?bool $deliveryaddress): self
    {
        $this->deliveryaddress = $deliveryaddress;

        return $this;
    }

    public function getInvoiceaddress(): ?bool
    {
        return $this->invoiceaddress;
    }

    public function setInvoiceaddress(?bool $invoiceaddress): self
    {
        $this->invoiceaddress = $invoiceaddress;

        return $this;
    }
}
