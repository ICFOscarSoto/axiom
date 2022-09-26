<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPBuyOrders;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPVariants;
use \App\Modules\ERP\Entity\ERPStores;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPBuyOrdersLinesRepository")
 */
class ERPBuyOrdersLines
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $linenum;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPBuyOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $buyorder;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPVariants")
     */
    private $variant;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $variantname;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $variantvalue;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $productname;

    /**
     * @ORM\Column(type="float")
     */
    private $quantity;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $shoppingprice;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total;

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
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount2;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount3;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount4;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discountequivalent;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $totaldiscount;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $supplierreference;

    /**
     * @ORM\Column(type="float")
     */
    private $pvp;

    /**
     * @ORM\Column(type="float")
     */
    private $subtotal;

    /**
     * @ORM\Column(type="float")
     */
    private $taxperc;

    /**
     * @ORM\Column(type="float")
     */
    private $taxunit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packing;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $multiplicity;


    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minimumquantityofbuy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purchaseunit;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateestimated;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $weight;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $store;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $storecode;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $storename;
    /**
     * @ORM\Column(type="string", length=32)
     */
    private $purchasemeasure;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLinenum(): ?int
    {
        return $this->linenum;
    }

    public function setLinenum(int $linenum): self
    {
        $this->linenum = $linenum;

        return $this;
    }

    public function getBuyorder(): ?ERPBuyOrders
    {
        return $this->buyorder;
    }

    public function setBuyorder(?ERPBuyOrders $buyorder): self
    {
        $this->buyorder = $buyorder;

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

    public function getVariant(): ?ERPVariants
    {
        return $this->variant;
    }

    public function setVariant(?ERPVariants $variant): self
    {
        $this->variant = $variant;

        return $this;
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

    public function getProductname(): ?string
    {
        return $this->productname;
    }

    public function setProductname(string $productname): self
    {
        $this->productname = $productname;

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

    public function getShoppingprice(): ?float
    {
        return $this->shoppingprice;
    }

    public function setShoppingprice(?float $shoppingprice): self
    {
        $this->shoppingprice = $shoppingprice;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
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

    public function getDiscount1(): ?float
    {
        return $this->discount1;
    }

    public function setDiscount1(?float $discount1): self
    {
        $this->discount1 = $discount1;

        return $this;
    }

    public function getDiscount2(): ?float
    {
        return $this->discount2;
    }

    public function setDiscount2(?float $discount2): self
    {
        $this->discount2 = $discount2;

        return $this;
    }

    public function getDiscount3(): ?float
    {
        return $this->discount3;
    }

    public function setDiscount3(?float $discount3): self
    {
        $this->discount3 = $discount3;

        return $this;
    }

    public function getDiscount4(): ?float
    {
        return $this->discount4;
    }

    public function setDiscount4(?float $discount4): self
    {
        $this->discount4 = $discount4;

        return $this;
    }

    public function getDiscountequivalent(): ?float
    {
        return $this->discountequivalent;
    }

    public function setDiscountequivalent(?float $discountequivalent): self
    {
        $this->discountequivalent = $discountequivalent;

        return $this;
    }

    public function getTotaldiscount(): ?float
    {
        return $this->totaldiscount;
    }

    public function setTotaldiscount(?float $totaldiscount): self
    {
        $this->totaldiscount = $totaldiscount;

        return $this;
    }

    public function getSupplierreference(): ?string
    {
        return $this->supplierreference;
    }

    public function setSupplierreference(string $supplierreference): self
    {
        $this->supplierreference = $supplierreference;

        return $this;
    }

    public function getPvp(): ?float
    {
        return $this->pvp;
    }

    public function setPvp(float $pvp): self
    {
        $this->pvp = $pvp;

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

    public function getPacking(): ?int
    {
        return $this->packing;
    }

    public function setPacking(?int $packing): self
    {
        $this->packing = $packing;

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

    public function getDateestimated(): ?\DateTimeInterface
    {
        return $this->dateestimated;
    }

    public function setDateestimated(?\DateTimeInterface $dateestimated): self
    {
        $this->dateestimated = $dateestimated;

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

    public function getStore(): ?ERPStores
    {
        return $this->store;
    }

    public function setStore(?ERPStores $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function getStorecode(): ?string
    {
        return $this->storecode;
    }

    public function setStorecode(string $storecode): self
    {
        $this->storecode = $storecode;

        return $this;
    }

    public function getStorename(): ?string
    {
        return $this->storename;
    }

    public function setStorename(string $storename): self
    {
        $this->storename = $storename;

        return $this;
    }

    public function getVariantname(): ?string
    {
        return $this->variantname;
    }

    public function setVariantname(string $variantname): self
    {
        $this->variantname = $variantname;

        return $this;
    }

    public function getVariantvalue(): ?string
    {
        return $this->variantvalue;
    }

    public function setVariantvalue(string $variantvalue): self
    {
        $this->variantvalue = $variantvalue;

        return $this;
    }

    public function getPurchasemeasure(): ?string
    {
        return $this->purchasemeasure;
    }

    public function setPurchasemeasure(string $purchasemeasure): self
    {
        $this->purchasemeasure = $purchasemeasure;

        return $this;
    }
}
