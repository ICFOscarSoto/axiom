<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPBuyOrders;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPVariantsValues;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\ERP\Entity\ERPBuyOrdersLinesRepository")
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPVariantsValues")
     */
    private $variant;

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
    private $served;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pendingserve;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="float")
     */
    private $virtualstock;

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
    private $totaldiscount;

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

    public function getVariant(): ?ERPVariantsValues
    {
        return $this->variant;
    }

    public function setVariant(?ERPVariantsValues $variant): self
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

    public function getServed(): ?float
    {
        return $this->served;
    }

    public function setServed(?float $served): self
    {
        $this->served = $served;

        return $this;
    }

    public function getPendingserve(): ?float
    {
        return $this->pendingserve;
    }

    public function setPendingserve(?float $pendingserve): self
    {
        $this->pendingserve = $pendingserve;

        return $this;
    }

    public function getStock(): ?float
    {
        return $this->stock;
    }

    public function setStock(?float $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getVirtualstock(): ?float
    {
        return $this->virtualstock;
    }

    public function setVirtualstock(float $virtualstock): self
    {
        $this->virtualstock = $virtualstock;

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

    public function getTotaldiscount(): ?float
    {
        return $this->totaldiscount;
    }

    public function setTotaldiscount(?float $totaldiscount): self
    {
        $this->totaldiscount = $totaldiscount;

        return $this;
    }
}
