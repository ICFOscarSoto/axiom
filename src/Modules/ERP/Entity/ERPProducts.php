<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPManufacturers;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPCategories;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\HR\Entity\HRWorkers;
use \App\Modules\Globale\Entity\GlobaleTaxes;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPProductsRepository")
 */
class ERPProducts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onsale=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onbuy=1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $weight;

    /**
     * @ORM\Column(type="boolean")
     */
    private $traceability;

    /**
     * @ORM\Column(type="boolean")
     */
    private $grouped;

    /**
     * @ORM\Column(type="boolean")
     */
    private $expiration;

    /**
     * @ORM\Column(type="boolean")
     */
    private $discontinued;

    /**
     * @ORM\Column(type="boolean")
     */
    private $margincontrol=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $stockcontrol=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $saleindecimals;

    /**
     * @ORM\Column(type="boolean")
     */
    private $exclusiveonline;

    /**
     * @ORM\Column(type="boolean")
     */
    private $bigsize;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active=1;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minimumquantityofsale;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPManufacturers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $manufacturer;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCategories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleTaxes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $taxes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $salepacking;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $multiplicity;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $promotion;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $rotation;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $PVP;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $PVPR;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $shoppingPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    private $netprice;


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

    public function getOnsale(): ?bool
    {
        return $this->onsale;
    }

    public function setOnsale(bool $onsale): self
    {
        $this->onsale = $onsale;

        return $this;
    }

    public function getOnbuy(): ?bool
    {
        return $this->onbuy;
    }

    public function setOnbuy(bool $onbuy): self
    {
        $this->onbuy = $onbuy;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getTraceability(): ?bool
    {
        return $this->traceability;
    }

    public function setTraceability(bool $traceability): self
    {
        $this->traceability = $traceability;

        return $this;
    }

    public function getGrouped(): ?bool
    {
        return $this->grouped;
    }

    public function setGrouped(bool $grouped): self
    {
        $this->grouped = $grouped;

        return $this;
    }

    public function getExpiration(): ?bool
    {
        return $this->expiration;
    }

    public function setExpiration(bool $expiration): self
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function getDiscontinued(): ?bool
    {
        return $this->discontinued;
    }

    public function setDiscontinued(bool $discontinued): self
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    public function getMargincontrol(): ?bool
    {
        return $this->margincontrol;
    }

    public function setMargincontrol(bool $margincontrol): self
    {
        $this->margincontrol = $margincontrol;

        return $this;
    }

    public function getStockcontrol(): ?bool
    {
        return $this->stockcontrol;
    }

    public function setStockcontrol(bool $stockcontrol): self
    {
        $this->stockcontrol = $stockcontrol;

        return $this;
    }

    public function getSaleindecimals(): ?bool
    {
        return $this->saleindecimals;
    }

    public function setSaleindecimals(bool $saleindecimals): self
    {
        $this->saleindecimals = $saleindecimals;

        return $this;
    }

    public function getExclusiveonline(): ?bool
    {
        return $this->exclusiveonline;
    }

    public function setExclusiveonline(bool $exclusiveonline): self
    {
        $this->exclusiveonline = $exclusiveonline;

        return $this;
    }

    public function getBigsize(): ?bool
    {
        return $this->bigsize;
    }

    public function setBigsize(bool $bigsize): self
    {
        $this->bigsize = $bigsize;

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

    public function getMinimumquantityofsale(): ?int
    {
        return $this->minimumquantityofsale;
    }

    public function setMinimumquantityofsale(?int $minimumquantityofsale): self
    {
        $this->minimumquantityofsale = $minimumquantityofsale;

        return $this;
    }

    public function getManufacturer(): ?ERPManufacturers
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?ERPManufacturers $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

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

    public function getCategory(): ?ERPCategories
    {
        return $this->category;
    }

    public function setCategory(?ERPCategories $category): self
    {
        $this->category = $category;

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


    public function getTaxes(): ?GlobaleTaxes
    {
        return $this->taxes;
    }

    public function setTaxes(?GlobaleTaxes $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    public function getSalepacking(): ?int
    {
        return $this->salepacking;
    }

    public function setSalepacking(?int $salepacking): self
    {
        $this->salepacking = $salepacking;

        return $this;
    }

    public function getMultiplicity(): ?int
    {
        return $this->multiplicity;
    }

    public function setMultiplicity(?int $multiplicity): self
    {
        $this->multiplicity = $multiplicity;

        return $this;
    }

    public function getPromotion(): ?bool
    {
        return $this->promotion;
    }

    public function setPromotion(?bool $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getRotation(): ?bool
    {
        return $this->rotation;
    }

    public function setRotation(?bool $rotation): self
    {
        $this->rotation = $rotation;

        return $this;
    }

    public function getPVP(): ?float
    {
        return $this->PVP;
    }

    public function setPVP(float $PVP): self
    {
        $this->PVP = $PVP;

        return $this;
    }

    public function getPVPR(): ?float
    {
        return $this->PVPR;
    }

    public function setPVPR(?float $PVPR): self
    {
        $this->PVPR = $PVPR;

        return $this;
    }

    public function getShoppingPrice(): ?float
    {
        return $this->shoppingPrice;
    }

    public function setShoppingPrice(?float $shoppingPrice): self
    {
        $this->shoppingPrice = $shoppingPrice;

        return $this;
    }

    public function priceCalculated($doctrine){
      $repository=$doctrine->getRepository(ERPShoppingDiscounts::class);
      //Search in the treeCategories which is the most specific with ShoppingDiscounts
      $repositoryCategory=$doctrine->getRepository(ERPCategories::class);
      $category=$this->category;
      $shoppingDiscounts=$repository->findOneBy(["supplier"=>$this->supplier,"category"=>$category,"active"=>1,"deleted"=>0]);
      while ($category->getParentid()!=null && $shoppingDiscounts==null){
          $category=$category->getParentid();
          $shoppingDiscounts=$repository->findOneBy(["supplier"=>$this->supplier,"category"=>$category,"active"=>1,"deleted"=>0]);
      }
      if ($shoppingDiscounts==null)
          $shoppingDiscounts=$repository->findOneBy(["supplier"=>$this->supplier,"active"=>1,"deleted"=>0]);

      $this->setShoppingPrice($this->PVPR*(1-$shoppingDiscounts->getDiscount()/100));
    }

    public function preProccess($kernel, $doctrine, $user, $params, $oldobj){

      //If PVPR, Category or Suppliers is updated then ShoppingPrice is calculated
      if(($this->PVPR!=$oldobj->getPVPR() or $this->category!=$oldobj->getCategory() or $this->supplier!=$oldobj->getSupplier()))
          $this->priceCalculated($doctrine);

    }

     public function formValidation($kernel, $doctrine, $user, $validationParams){
       $repository=$doctrine->getRepository(ERPProducts::class);
       $product=$repository->findOneBy(["code"=>$this->code,"company"=>$user->getCompany(),"active"=>1,"deleted"=>0]);
       if($product!=null and $product->id!=$this->id)
         return ["valid"=>false, "global_errors"=>["El producto ya existe"]];
       else return ["valid"=>true];
     }

     public function getNetprice(): ?bool
     {
         return $this->netprice;
     }

     public function setNetprice(bool $netprice): self
     {
         $this->netprice = $netprice;

         return $this;
     }


}
