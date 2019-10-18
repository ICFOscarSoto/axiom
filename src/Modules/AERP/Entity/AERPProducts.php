<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\AERP\Entity\AERPProviders;
use \App\Modules\Globale\Entity\GlobaleTaxes;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\AERP\Entity\AERPWarehouseLocations;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\AERP\Entity\AERPWarehouses;

/**
 * @ORM\Entity(repositoryClass="App\Modules\AERP\Repository\AERPProductsRepository")
 */
class AERPProducts
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
     * @ORM\Column(type="string", length=125)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPProviders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $provider;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onsale=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onpurchase=1;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleTaxes")
     */
    private $tax;

    /**
     * @ORM\Column(type="float")
     */
    private $purchaseprice=0;

    /**
     * @ORM\Column(type="float")
     */
    private $price=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $stockControl=1;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $service;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPWarehouseLocations")
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPWarehouses")
     */
    private $warehouse;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getProvider(): ?AERPProviders
    {
        return $this->provider;
    }

    public function setProvider(?AERPProviders $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getOnsale(): ?bool
    {
        return $this->onsale;
    }

    public function setOnsale(bool $onsale): self
    {
        $this->onsale = $onsale;

        return $this;
    }

    public function getOnpurchase(): ?bool
    {
        return $this->onpurchase;
    }

    public function setOnpurchase(bool $onpurchase): self
    {
        $this->onpurchase = $onpurchase;

        return $this;
    }

    public function getTax(): ?GlobaleTaxes
    {
        return $this->tax;
    }

    public function setTax(?GlobaleTaxes $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getPurchaseprice(): ?float
    {
        return $this->purchaseprice;
    }

    public function setPurchaseprice(float $purchaseprice): self
    {
        $this->purchaseprice = $purchaseprice;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStockcontrol(): ?bool
    {
        return $this->stockControl;
    }

    public function setStockcontrol(bool $stockControl): self
    {
        $this->stockControl = $stockControl;

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

    public function getService(): ?bool
    {
        return $this->service;
    }

    public function setService(bool $service): self
    {
        $this->service = $service;

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

    public function formValidation($kernel, $doctrine, $user, $validationParams){
      $repository=$doctrine->getRepository(AERPProducts::class);
      $product=$repository->findOneBy(["code"=>$this->code,"company"=>$user->getCompany(),"deleted"=>0]);
      if($product!=null and $product->id!=$this->id)
        return ["valid"=>false, "global_errors"=>["El producto ya existe"]];
      else {

        return ["valid"=>true];
      }

    }

    public function getLocation(): ?AERPWarehouseLocations
    {
        return $this->location;
    }

    public function setLocation(?AERPWarehouseLocations $location): self
    {
        $this->location = $location;

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

    public function getWarehouse(): ?AERPWarehouses
    {
        return $this->warehouse;
    }

    public function setWarehouse(?AERPWarehouses $warehouse): self
    {
        $this->warehouse = $warehouse;

        return $this;
    }
}
