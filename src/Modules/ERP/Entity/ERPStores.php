<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleStates;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPStoresManagers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStoresRepository")
 */
class ERPStores
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=120)
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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $phone;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

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

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $inventorymanager;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $managed;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoresManagers")
     */
    private $managedBy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transfernotifyaddress;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $typeofnotifications;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

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

    public function getInventorymanager(): ?GlobaleUsers
    {
        return $this->inventorymanager;
    }

    public function setInventorymanager(?GlobaleUsers $inventorymanager): self
    {
        $this->inventorymanager = $inventorymanager;

        return $this;
    }

    public function getManaged(): ?bool
    {
        return $this->managed;
    }

    public function setManaged(?bool $managed): self
    {
        $this->managed = $managed;

        return $this;
    }

    public function getManagedBy(): ?ERPStoresManagers
    {
        return $this->managedBy;
    }

    public function setManagedBy(?ERPStoresManagers $managedBy): self
    {
        $this->managedBy = $managedBy;

        return $this;
    }

    public function getTransfernotifyaddress(): ?string
    {
        return $this->transfernotifyaddress;
    }

    public function setTransfernotifyaddress(?string $transfernotifyaddress): self
    {
        $this->transfernotifyaddress = $transfernotifyaddress;

        return $this;
    }

    public function getTypeofnotifications(): ?int
    {
        return $this->typeofnotifications;
    }

    public function setTypeofnotifications(?int $typeofnotifications): self
    {
        $this->typeofnotifications = $typeofnotifications;

        return $this;
    }
}
