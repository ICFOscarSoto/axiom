<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPPurchasesOrders;
use \App\Modules\ERP\Entity\ERPProducts;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\ERP\Entity\ERPPurchasesOrdersLinesRepository")
 */
class ERPPurchasesOrdersLines
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPurchasesOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $purchasesorder;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     */
    private $product;

    /**
     * @ORM\Column(type="float")
     */
    private $unitprice=0;

    /**
     * @ORM\Column(type="float")
     */
    private $quantity=1;

    /**
     * @ORM\Column(type="float")
     */
    private $taxperc=0;

    /**
     * @ORM\Column(type="float")
     */
    private $taxunit=0;

    /**
     * @ORM\Column(type="float")
     */
    private $irpfperc=0;

    /**
     * @ORM\Column(type="float")
     */
    private $irpfunit=0;

    /**
     * @ORM\Column(type="float")
     */
    private $surchargeperc=0;

    /**
     * @ORM\Column(type="float")
     */
    private $surchargeunit=0;

    /**
     * @ORM\Column(type="float")
     */
    private $subtotal=0;

    /**
     * @ORM\Column(type="float")
     */
    private $total=0;

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
     * @ORM\Column(type="string", length=250)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $dtoperc=0;

    /**
     * @ORM\Column(type="float")
     */
    private $dtounit=0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $linenum;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $code;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchasesorder(): ?ERPPurchasesOrders
    {
        return $this->purchasesorder;
    }

    public function setPurchasesorder(?ERPPurchasesOrders $purchasesorder): self
    {
        $this->purchasesorder = $purchasesorder;

        return $this;
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

    public function getUnitprice(): ?float
    {
        return $this->unitprice;
    }

    public function setUnitprice(float $unitprice): self
    {
        $this->unitprice = $unitprice;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTaxperc(): ?float
    {
        return $this->taxperc;
    }

    public function setTaxperc(float $taxperc): self
    {
        $this->taxperc = $taxperc;

        return $this;
    }

    public function getTaxunit(): ?float
    {
        return $this->taxunit;
    }

    public function setTaxunit(float $taxunit): self
    {
        $this->taxunit = $taxunit;

        return $this;
    }

    public function getIrpfperc(): ?float
    {
        return $this->irpfperc;
    }

    public function setIrpfperc(float $irpfperc): self
    {
        $this->irpfperc = $irpfperc;

        return $this;
    }

    public function getIrpfunit(): ?float
    {
        return $this->irpfunit;
    }

    public function setIrpfunit(float $irpfunit): self
    {
        $this->irpfunit = $irpfunit;

        return $this;
    }

    public function getSurchargeperc(): ?float
    {
        return $this->surchargeperc;
    }

    public function setSurchargeperc(float $surchargeperc): self
    {
        $this->surchargeperc = $surchargeperc;

        return $this;
    }

    public function getSurchargeunit(): ?float
    {
        return $this->surchargeunit;
    }

    public function setSurchargeunit(float $surchargeunit): self
    {
        $this->surchargeunit = $surchargeunit;

        return $this;
    }

    public function getSubtotal(): ?float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): self
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDtoperc(): ?float
    {
        return $this->dtoperc;
    }

    public function setDtoperc(float $dtoperc): self
    {
        $this->dtoperc = $dtoperc;

        return $this;
    }

    public function getDtounit(): ?float
    {
        return $this->dtounit;
    }

    public function setDtounit(float $dtounit): self
    {
        $this->dtounit = $dtounit;

        return $this;
    }

    public function getLinenum(): ?int
    {
        return $this->linenum;
    }

    public function setLinenum(?int $linenum): self
    {
        $this->linenum = $linenum;

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
}
