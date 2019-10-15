<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\AERP\Entity\AERPProviders;
use \App\Modules\Globale\Entity\GlobaleTaxes;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\AERP\Entity\AERPProductsRepository")
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
    private $onsale;

    /**
     * @ORM\Column(type="boolean")
     */
    private $onpurchase;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleTaxes")
     */
    private $tax;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     */
    private $stockControl;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
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


      }

    }
}
