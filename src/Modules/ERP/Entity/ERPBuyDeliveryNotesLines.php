<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPBuyDeliveryNotes;
use \App\Modules\ERP\Entity\ERPBuyDeliveryNotesLines;
use \App\Modules\ERP\Entity\ERPProductsVariants;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\ERP\Entity\ERPBuyOrdersLines;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPBuyDeliveryNotesLinesRepository")
 */
class ERPBuyDeliveryNotesLines
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPBuyDeliveryNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $buydeliverynote;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPBuyDeliveryNotesLines")
     * @ORM\JoinColumn(nullable=true)
     */
    private $buydeliverynoteline;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProductsVariants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $store;

    /**
     * @ORM\Column(type="integer")
     */
    private $linenum;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $productcode;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $productname;
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $varianttype;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $variantname;

    /**
     * @ORM\Column(type="float")
     */
    private $quantity;

    /**
     * @ORM\Column(type="float")
     */
    private $quantityconfirmed;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $amount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discountperc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discountunit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $base;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxperc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $taxunit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datefinish;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $storecode;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $storename;

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

    public function getBuydeliverynote(): ?ERPBuyDeliveryNotes
    {
        return $this->buydeliverynote;
    }

    public function setBuydeliverynote(?ERPBuyDeliveryNotes $buydeliverynote): self
    {
        $this->buydeliverynote = $buydeliverynote;

        return $this;
    }

    public function getBuydeliverynoteline(): ?ERPBuyDeliveryNotesLines
    {
        return $this->buydeliverynoteline;
    }

    public function setBuydeliverynoteline(?ERPBuyDeliveryNotesLines $buydeliverynoteline): self
    {
        $this->buydeliverynoteline = $buydeliverynoteline;

        return $this;
    }

    public function getProductvariant(): ?ERPProductsVariants
    {
        return $this->productvariant;
    }

    public function setProductvariant(?ERPProductsVariants $productvariant): self
    {
        $this->productvariant = $productvariant;

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

    public function getLinenum(): ?int
    {
        return $this->linenum;
    }

    public function setLinenum(int $linenum): self
    {
        $this->linenum = $linenum;

        return $this;
    }

    public function getProductcode(): ?string
    {
        return $this->productcode;
    }

    public function setProductcode(string $code): self
    {
        $this->productcode = $productcode;

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

    public function getVarianttype(): ?string
    {
        return $this->varianttype;
    }

    public function setVarianttype(string $varianttype): self
    {
        $this->varianttype = $varianttype;

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

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantityconfirmed(): ?float
    {
        return $this->quantityconfirmed;
    }

    public function setQuantityconfirmed(float $quantityconfirmed): self
    {
        $this->quantityconfirmed = $quantityconfirmed;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDiscountperc(): ?float
    {
        return $this->discountperc;
    }

    public function setDiscountperc(float $discountperc): self
    {
        $this->discountperc = $discountperc;

        return $this;
    }

    public function getBase(): ?float
    {
        return $this->base;
    }

    public function setBase(?float $base): self
    {
        $this->base = $base;

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

    public function getDiscountunit(): ?float
    {
        return $this->discountunit;
    }

    public function setDiscountunit(float $discountunit): self
    {
        $this->discountunit = $discountunit;

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

    public function getDatefinish(): ?\DateTimeInterface
    {
        return $this->datefinish;
    }

    public function setDatefinish(\DateTimeInterface $datefinish): self
    {
        $this->datefinish = $datefinish;

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
}
