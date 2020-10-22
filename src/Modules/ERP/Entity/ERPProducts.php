<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPManufacturers;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPCategories;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPProductPrices;
use \App\Modules\ERP\Entity\ERPCustomerGroups;
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
    private $traceability=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $grouped=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $expiration=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $discontinued=0;

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
    private $saleindecimals=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $exclusiveonline=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $bigsize=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted=0;

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
    private $minimumquantityofsale=1;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPManufacturers")
     * @ORM\JoinColumn(onDelete="SET NULL")
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCategories", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleTaxes")
     * @ORM\JoinColumn(onDelete="SET NULL")
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
    private $netprice=0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pvpincrement;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\ERP\Entity\ERPStocks", mappedBy="product")
     */
    private $stocks;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $checkweb;

    public function getStockcampollano($doctrine): ?int
    {
        $stockRepository=$doctrine->getRepository('\App\Modules\ERP\Entity\ERPStocks');
        $quantity=$stockRepository->findOneBy(["product"=>$this]);
        return $quantity==null?0:$quantity->getQuantity();
    }

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


    public function getShoppingDiscount($doctrine) {
      $repository=$doctrine->getRepository(ERPShoppingDiscounts::class);
      //Search in the treeCategories which is the most specific with ShoppingDiscounts
      $repositoryCategory=$doctrine->getRepository(ERPCategories::class);
      $category=$this->category;
      $shoppingDiscounts=$repository->findOneBy(["supplier"=>$this->supplier,"category"=>$category,"active"=>1,"deleted"=>0]);
      if ($category!=null)
      while ($category->getParentid()!=null && $shoppingDiscounts==null){
          $category=$category->getParentid();
          $shoppingDiscounts=$repository->findOneBy(["supplier"=>$this->supplier,"category"=>$category,"active"=>1,"deleted"=>0]);
      }
      if ($shoppingDiscounts==null)
          $shoppingDiscounts=$repository->findOneBy(["supplier"=>$this->supplier,"active"=>1,"deleted"=>0]);
      return $shoppingDiscounts!=null?$shoppingDiscounts->getDiscount():0;
    }


    /*permite recalcular el precio de compra y el PVP*/
    public function priceCalculated($doctrine)
    {
      $em = $doctrine->getManager();
      $newShoppingPrice=$this->PVPR*(1-$this->getShoppingDiscount($doctrine)/100);
      $this->setShoppingPrice($newShoppingPrice);
      /*ante un cambio en los precios, si no tenemos almacenado el valor del incremento máximo
      para el producto, tendremos que recalcularlo. En cambio, si ya lo tenemos almacenado, simplemente se recalculará
      el PVP con ese incremento y con el nuevo precio de compra*/
      if($this->getPvpincrement()==NULL)
      {
        $CustomerGroupsRepository=$doctrine->getRepository(ERPCustomerGroups::class);
        $customergroups=$CustomerGroupsRepository->findBy(["active"=>1,"deleted"=>0]);
        $maxincrement=0;
        foreach($customergroups as $customergroup){
          $increment=$this->getMaxIncrement($doctrine,$customergroup);
          if($increment>$maxincrement) $maxincrement=$increment;
        }
        $this->setPvpincrement($maxincrement);
        $this->setPVP($newShoppingPrice*(1+($maxincrement/100)));
      }
      else $this->setPVP($newShoppingPrice*(1+($this->getPvpincrement()/100)));
      //Una vez recalculado el PVP, tenemos que recalcular el precio para cada incremento de grupo que exista
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repositoryProductPrices=$doctrine->getRepository(ERPProductPrices::class);
      if ($this->supplier!=null){
        $productprices=$repositoryProductPrices->pricesByProductIdAndSupplier($this->getId(),$this->supplier->getId());
      foreach($productprices as $productprice)
      {
          $productpriceEntity=$repositoryProductPrices->findOneBy(["id"=>$productprice]);
          $productpriceEntity->setPrice(round($newShoppingPrice*(1+($productpriceEntity->getIncrement()/100)),2));
      }
}
    }


    /*permite recalcular el precio de compra, el incremento y el PVP cuando cambiamos de proveedor principal*/
    public function priceCalculatedNewMainSupplier($doctrine)
    {
      $em = $doctrine->getManager();
      $newShoppingPrice=$this->PVPR*(1-$this->getShoppingDiscount($doctrine)/100);
      $this->setShoppingPrice($newShoppingPrice);


      $CustomerGroupsRepository=$doctrine->getRepository(ERPCustomerGroups::class);
      $customergroups=$CustomerGroupsRepository->findBy(["active"=>1,"deleted"=>0]);
      $maxincrement=0;
      foreach($customergroups as $customergroup){
        $increment=$this->getMaxIncrement($doctrine,$customergroup);
        if($increment>$maxincrement) $maxincrement=$increment;
      }

      $this->setPvpincrement($maxincrement);
      $this->setPVP($newShoppingPrice*(1+($maxincrement/100)));

      //Una vez recalculado el PVP, tenemos que recalcular el precio para cada incremento de grupo que exista
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repositoryProductPrices=$doctrine->getRepository(ERPProductPrices::class);
      $productprices=$repositoryProductPrices->pricesByProductIdAndSupplier($this->getId(),$this->supplier->getId());

      foreach($productprices as $productprice)
      {
          $productpriceEntity=$repositoryProductPrices->findOneBy(["id"=>$productprice]);
          $productpriceEntity->setPrice(round($newShoppingPrice*(1+($productpriceEntity->getIncrement()/100)),2));
      }

    }


    public function calculatePVP($doctrine){
         $CustomerGroupsRepository=$doctrine->getRepository(ERPCustomerGroups::class);
         $customergroups=$CustomerGroupsRepository->findBy(["active"=>1,"deleted"=>0]);
         $maxincrement=0;
         foreach($customergroups as $customergroup){
           $increment=$this->getMaxIncrement($doctrine,$customergroup);
           //dump("Incremento ".$increment." para el grupo ".$customergroup->getName());
           if($increment>$maxincrement) $maxincrement=$increment;
         }
         $this->setPVP($this->shoppingPrice*(1+($maxincrement/100)));
         $this->setPvpincrement($maxincrement);
     }

    public function getMaxIncrement($doctrine,$customergroup){
      $repository=$doctrine->getRepository(ERPIncrements::class);
      $category=$this->category;
      $maxincrement=$repository->getMaxIncrement($this->supplier,$category,$customergroup);
      if($category!=null){
      while ($category->getParentid()!=null && $maxincrement==null){
          $category=$category->getParentid();
          $maxincrement=$repository->getMaxIncrement($this->supplier,$category,$customergroup);
      }

      if ($maxincrement==null){

          $maxincrement=$repository->getMaxIncrement($this->supplier,null,$customergroup);

      }
      if ($maxincrement==null){
          $category=$this->category;
          $maxincrement=$repository->getMaxIncrement(null,$category,$customergroup);

          while ($category->getParentid()!=null && $maxincrement==null){
              $category=$category->getParentid();
              $maxincrement=$repository->getMaxIncrement(null,$category,$customergroup);
          }
       }
     }
      return $maxincrement;
    }


    public function preProccess($kernel, $doctrine, $user, $params, $oldobj){
      //si cambia el PVPR o la categoria, recalculamos precios
      if(($this->PVPR!=$oldobj->getPVPR() or $this->category!=$oldobj->getCategory()))
          $this->priceCalculated($doctrine);
      //en caso de que cambie el proveedor preferente, tenemos que tratarlo de manera diferente, ya que hay
      //que recalcular el incremento y el PVP en la tabla del producto.
      else if($this->supplier!=$oldobj->getSupplier())
          $this->priceCalculatedNewMainSupplier($doctrine);

    }
    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){
      $this->calculateIncrementByProduct($doctrine);
      $this->calculateCustomerIncrementsByProduct($doctrine);
      if($this->getCheckweb()) $this->checkWebProduct($doctrine);
    }

     public function formValidation($kernel, $doctrine, $user, $validationParams){
       $repository=$doctrine->getRepository(ERPProducts::class);
       $product=$repository->findOneBy(["code"=>$this->code,"company"=>$user->getCompany(),"active"=>1,"deleted"=>0]);
       if($product!=null and $product->id!=$this->id)
         return ["valid"=>false, "global_errors"=>["El producto ya existe"]];
       else {
       $fieldErrors=[];
         if($this->supplier==null){
           $fieldErrors["supplier"]="This field is required.";
         }/*
         if($this->manufacturer==null){
           $fieldErrors["manufacturer"]="This field is required.";
         }*/

         if($this->category==null){
           $fieldErrors["category"]="This field is required.";
         }
         if($this->taxes==null){
           $fieldErrors["taxes"]="This field is required.";
         }
         /*if($this->shoppingPrice==0){
           $fieldErrors["shoppingPrice"]="This field is required.";
         }*/

         if (empty($fieldErrors)) return ["valid"=>true];
           else return ["valid"=>false, "field_errors"=>$fieldErrors];
       }

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

     public function getPvpincrement(): ?float
     {
         return $this->pvpincrement;
     }

     public function setPvpincrement(?float $pvpincrement): self
     {
         $this->pvpincrement = $pvpincrement;

         return $this;
     }

     //método creado específicamente para actualizar los precios de un producto concreto, cuando se vea afectado su precio de compra.
     public function calculateIncrementByProduct($doctrine){
       $em = $doctrine->getManager();
       $repositoryProductPrices=$doctrine->getRepository(ERPProductPrices::class);
       $repositoryCustomerGroups=$doctrine->getRepository(ERPCustomerGroups::class);
       $repositorySuppliers=$doctrine->getRepository(ERPSuppliers::class);
       $repositoryIncrements=$doctrine->getRepository(ERPIncrements::class);
       $repositoryCustomerGroups=$doctrine->getRepository(ERPCustomerGroups::class);
       $repositorySuppliers=$doctrine->getRepository(ERPSuppliers::class);
       $supplier=$repositorySuppliers->findOneBy(["id"=>$this->supplier]);
       $repositoryCategories=$doctrine->getRepository(ERPCategories::class);
       $category=$repositoryCategories->findOneBy(["id"=>$this->category]);
       if($this->category!=null)
        {
           $this->calculatePVP($doctrine);
           $customergroups=$repositoryCustomerGroups->findBy(["active"=>1,"deleted"=>0]);

           foreach($customergroups as $customergroup){
             $increment=$repositoryIncrements->getIncrementByGroup($supplier,$category,$customergroup);
             if($increment!=NULL){
               if($repositoryProductPrices->existPrice($this,$customergroup,$supplier)){
                     $productpricesEntity=$repositoryProductPrices->findOneBy(["product"=>$this,"customergroup"=>$customergroup,"supplier"=>$supplier]);
                     $productpricesEntity->setIncrement($increment);
                     $productpricesEntity->setPrice(round($this->shoppingPrice*(1+($increment/100)),2));
                 }
               else {
                     $productpricesEntity= new ERPProductPrices();
                     $productpricesEntity->setProduct($this);
                     $productpricesEntity->setCustomergroup($customergroup);
                     $productpricesEntity->setSupplier($supplier);
                     $productpricesEntity->setIncrement($increment*1);
                     $productpricesEntity->setPrice(round($this->shoppingPrice*(1+($increment/100)),2));
                     $productpricesEntity->setActive(1);
                     $productpricesEntity->setDeleted(0);
                     $productpricesEntity->setDateupd(new \DateTime());
                     $productpricesEntity->setDateadd(new \DateTime());

               }
                   $em->persist($productpricesEntity);
             }
             $em->flush();
          }

         }

     }


  //método creado específicamente para actualizar los precios específicos de clientes para un producto concreto, cuando se vea afectado su precio de compra.
   public function calculateCustomerIncrementsByProduct($doctrine){
         $em = $doctrine->getManager();
         $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
         $repositoryCustomerPrices=$doctrine->getRepository(ERPCustomerPrices::class);
         $repositoryCustomers=$doctrine->getRepository(ERPCustomers::class);
         $repositorySuppliers=$doctrine->getRepository(ERPSuppliers::class);
         $repositoryIncrements=$doctrine->getRepository(ERPIncrements::class);
         $supplier=$repositorySuppliers->findOneBy(["id"=>$this->supplier]);
         $repositoryCategories=$doctrine->getRepository(ERPCategories::class);
         $repositoryCustomerIncrements=$doctrine->getRepository(ERPCustomerIncrements::class);
         $category=$repositoryCategories->findOneBy(["id"=>$this->category]);

         //de momento partimos de que el proveedor nunca es NULL, pero en el futuro tendremos
         //que permitir que pueda haber incrementos sólo por categría, por lo que esto no funcionaría.

         //controlamos que un producto no pueda recibir incrementos si no tiene una categoría asocaida
         if($this->getCategory()!=null)
         {

           $customers_ids=$repositoryCustomerPrices->findCustomersByProduct($this);
           foreach($customers_ids as $customer_id)
           {
             $customer=$repositoryCustomers->findOneBy(["id"=>$customer_id]);
             $customerincrement_obj=$repositoryCustomerIncrements->findOneBy(["supplier"=>$supplier,"category"=>$category,"customer"=>$customer]);
             $increment=$repositoryCustomerIncrements->getIncrementByCustomer($supplier,$category,$customer);
             if($increment!=NULL){
               if($repositoryCustomerPrices->existPrice($this,$customer,$supplier)){
                     $customerpricesEntity=$repositoryCustomerPrices->findOneBy(["product"=>$this,"customer"=>$customer,"supplier"=>$supplier]);
                     $customerpricesEntity->setIncrement($increment);
                     $customerpricesEntity->setPrice(round($this->getShoppingPrice()*(1+($increment/100)),2));
                     $customerpricesEntity->setStart($customerincrement_obj->getStart());
                     $customerpricesEntity->setEnd($customerincrement_obj->getEnd());
                 }
               else {
                     $customerpricesEntity= new ERPCustomerPrices();
                     $customerpricesEntity->setProduct($this);
                     $customerpricesEntity->setCustomer($customer);
                     $customerpricesEntity->setSupplier($supplier);
                     $customerpricesEntity->setIncrement($increment*1);
                     $customerpricesEntity->setPrice(round($this->getShoppingPrice()*(1+($increment/100)),2));
                     $customerpricesEntity->setActive(1);
                     $customerpricesEntity->setDeleted(0);
                     $customerpricesEntity->setDateupd(new \DateTime());
                     $customerpricesEntity->setDateadd(new \DateTime());
                     $customerpricesEntity->setStart($customerincrement_obj->getStart());
                     $customerpricesEntity->setEnd($customerincrement_obj->getEnd());

               }
                   $em->persist($customerpricesEntity);
             }

             //$em->persist($productEntity);
             $em->flush();

           }
         }


     }

  public function getCheckweb(): ?bool
  {
      return $this->checkweb;
  }

  public function setCheckweb(?bool $checkweb): self
  {
      $this->checkweb = $checkweb;

      return $this;
  }

   public function checkWebProduct($doctrine){
     $em = $doctrine->getManager();
     $repositoryWebProduct=$doctrine->getRepository(ERPWebProducts::class);
     $companyRepository=$doctrine->getRepository(GlobaleCompanies::class);
     $webproduct=$repositoryWebProduct->findOneBy(["product"=>$this]);

     if($webproduct==null){
       $obj=new ERPWebProducts();
       $obj->setProduct($this);
       $company=$companyRepository->find(2);
       $obj->setCompany($company);
       $obj->setDateadd(new \Datetime());
       $obj->setDateupd(new \Datetime());
       $obj->setDeleted(0);
       $obj->setActive(1);
       $em->persist($obj);
       $em->flush();
     }


   }


}
