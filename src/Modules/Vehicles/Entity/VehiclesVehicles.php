<?php

namespace App\Modules\Vehicles\Entity;

use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Vehicles\Entity\VehiclesFuels;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleCountries;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Vehicles\Repository\VehiclesVehiclesRepository")
 */
class VehiclesVehicles
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
     * @ORM\Column(type="string", length=100)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $licenseplate;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $licenseplatecountry;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $licenseplateenddate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $licenseplatedate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $vin;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(type="integer")
     */
    private $km;

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
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(type="integer")
     */
    private $fuelcapacity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Vehicles\Entity\VehiclesFuels")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fueltype;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRWorkers")
     */
    private $responsible;

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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getLicenseplate(): ?string
    {
        return $this->licenseplate;
    }

    public function setLicenseplate(string $licenseplate): self
    {
        $this->licenseplate = $licenseplate;

        return $this;
    }

    public function getLicenseplatecountry(): ?GlobaleCountries
    {
        return $this->licenseplatecountry;
    }

    public function setLicenseplatecountry(?GlobaleCountries $licenseplatecountry): self
    {
        $this->licenseplatecountry = $licenseplatecountry;

        return $this;
    }

    public function getLicenseplateenddate(): ?\DateTimeInterface
    {
        return $this->licenseplateenddate;
    }

    public function setLicenseplateenddate(?\DateTimeInterface $licenseplateenddate): self
    {
        $this->licenseplateenddate = $licenseplateenddate;

        return $this;
    }

    public function getLicenseplatedate(): ?\DateTimeInterface
    {
        return $this->licenseplatedate;
    }

    public function setLicenseplatedate(?\DateTimeInterface $licenseplatedate): self
    {
        $this->licenseplatedate = $licenseplatedate;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getKm(): ?int
    {
        return $this->km;
    }

    public function setKm(int $km): self
    {
        $this->km = $km;

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

    public function getFuelcapacity(): ?int
    {
        return $this->fuelcapacity;
    }

    public function setFuelcapacity(int $fuelcapacity): self
    {
        $this->fuelcapacity = $fuelcapacity;

        return $this;
    }

    public function encodeJson($doctrine){
      $array['id']=$this->id;
      $array['brand']=$this->brand;
      $array['model']=$this->model;
      $array['licenseplate']=$this->licenseplate;
      $array['vin']=$this->vin;
      $array['color']=$this->color;
      $array['fuelcapacity']=$this->fuelcapacity;
      if($this->fueltype)
        $array['fuel']=$this->fueltype->getName();
        else $array['fuel']='';
      return $array;
    }

    public function getFueltype(): ?VehiclesFuels
    {
        return $this->fueltype;
    }

    public function setFueltype(?VehiclesFuels $fueltype): self
    {
        $this->fueltype = $fueltype;

        return $this;
    }

    public function getResponsible(): ?HRWorkers
    {
        return $this->responsible;
    }

    public function setResponsible(?HRWorkers $responsible): self
    {
        $this->responsible = $responsible;

        return $this;
    }
}
