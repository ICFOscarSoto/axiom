<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Utils\ERPPrestashopUtils;
/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPOfferPricesRepository")
 */
class ERPOfferPrices
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $customer;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $increment;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?ERPProducts
    {
        return $this->product;
    }

    public function setProduct(?ERPProducts $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIncrement(): ?float
    {
        return $this->increment;
    }

    public function setIncrement(?float $increment): self
    {
        $this->increment = $increment;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $validationParams){
    $repository=$doctrine->getRepository(ERPOfferPrices::class);
    $exists=$repository->validOffer($this->id,$this->product,$this->customer, $this->quantity,$this->start,$this->end);
    if($exists)
      return ["valid"=>false, "global_errors"=>["Ya existe una oferta vigente para esos parámetros."]];
    if($this->end<$this->start AND $this->end!=NULL)
      return ["valid"=>false, "global_errors"=>["La fecha de fin debe ser posterior a la fecha de inicio."]];
    if($this->type==NULL)
      return ["valid"=>false, "global_errors"=>["Porfavor, elige un incremento o un precio neto."]];
    else return ["valid"=>true];


    }

    public function preProccess($kernel, $doctrine, $user, $params, $oldobj)
    {
      $repository=$doctrine->getRepository(ERPProducts::class);
      $product=$repository->findOneBy(["id"=>$this->product->getId(),"company"=>$user->getCompany(),"active"=>1,"deleted"=>0]);
      $this->price=round($product->getShoppingPrice()*(1+$this->increment/100),2);
      if($this->quantity==NULL OR $this->quantity<1) $this->quantity=1;
    }
/*
    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){


      if($this->getCustomer()==null) {
        $repository=$doctrine->getRepository(ERPProducts::class);
        $product=$repository->findOneBy(["id"=>$this->product->getId(),"company"=>$user->getCompany(),"active"=>1,"deleted"=>0]);
        $repositoryWebProduct=$doctrine->getRepository(ERPWebProducts::class);
        $webproduct=$repositoryWebProduct->findOneBy(["product"=>$product]);
        if($product->getCheckweb()) {
          $prestashopUtils= new ERPPrestashopUtils();
          $prestashopUtils->updateWebProductPrices($doctrine,$product,$webproduct);
        }
      }


    }
*/
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

}
