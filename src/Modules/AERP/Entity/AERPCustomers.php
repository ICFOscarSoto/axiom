<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\AERP\Entity\AERPContacts;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\AERP\Entity\AERPCustomerGroups;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPContacts")
     */
    private $shippcontact;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPCustomerGroups")
     */
    private $customergroup;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxrisk;

    /**
     * @ORM\Column(type="float")
     */
    private $risk;

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
     * @ORM\Column(type="string", length=175)
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

    public function getAgent(): ?GlobaleUsers
    {
        return $this->agent;
    }

    public function setAgent(?GlobaleUsers $agent): self
    {
        $this->agent = $agent;

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

    public function getShippcontact(): ?AERPContacts
    {
        return $this->shippcontact;
    }

    public function setShippcontact(?AERPContacts $shippcontact): self
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


}
