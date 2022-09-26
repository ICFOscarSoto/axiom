<?php

namespace App\Modules\ERP\Entity;

use App\Modules\Globale\Entity\GlobaleCompanies;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPProductsSuppliersRepository")
 */
class ERPProductsSuppliers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPproductsVariants", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPSuppliers", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

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
    private $active=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted=0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $multiplicity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minimumquantityofbuy=1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purchaseunit=1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $stockdate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductVariant(): ?ERPProductsVariants
    {
        return $this->productvariant;
    }

    public function setProductVariant(?ERPProductsVariants $productvariant): self
    {
        $this->productvariant = $productvariant;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

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

    public function getMultiplicity(): ?int
    {
        return $this->multiplicity;
    }

    public function setMultiplicity(?int $multiplicity): self
    {
        $this->multiplicity = $multiplicity;

        return $this;
    }

    public function getMinimumquantityofbuy(): ?int
    {
        return $this->minimumquantityofbuy;
    }

    public function setMinimumquantityofbuy(?int $minimumquantityofbuy): self
    {
        $this->minimumquantityofbuy = $minimumquantityofbuy;

        return $this;
    }

    public function getPurchaseunit(): ?int
    {
        return $this->purchaseunit;
    }

    public function setPurchaseunit(?int $purchaseunit): self
    {
        $this->purchaseunit = $purchaseunit;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getStockdate(): ?\DateTimeInterface
    {
        return $this->stockdate;
    }

    public function setStockdate(\DateTimeInterface $stockdate): self
    {
        $this->stockdate = $stockdate;

        return $this;
    }
}
