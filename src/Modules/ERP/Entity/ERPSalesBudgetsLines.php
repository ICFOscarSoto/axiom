<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSalesBudgets;
use \App\Modules\ERP\Entity\ERPProducts;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSalesBudgetsLinesRepository")
 */
class ERPSalesBudgetsLines
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSalesBudgets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $salesbudget;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="float")
     */
    private $unitprice;

    /**
     * @ORM\Column(type="float")
     */
    private $quantity;

    /**
     * @ORM\Column(type="float")
     */
    private $taxperc;

    /**
     * @ORM\Column(type="float")
     */
    private $taxunit;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $surchargeperc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $surchargeunit;

    /**
     * @ORM\Column(type="float")
     */
    private $subtotal;

    /**
     * @ORM\Column(type="float")
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSalesbudget(): ?ERPSalesBudgets
    {
        return $this->salesbudget;
    }

    public function setSalesbudget(?ERPSalesBudgets $salesbudget): self
    {
        $this->salesbudget = $salesbudget;

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

    public function getSurchargeperc(): ?float
    {
        return $this->surchargeperc;
    }

    public function setSurchargeperc(?float $surchargeperc): self
    {
        $this->surchargeperc = $surchargeperc;

        return $this;
    }

    public function getSurchargeunit(): ?float
    {
        return $this->surchargeunit;
    }

    public function setSurchargeunit(?float $surchargeunit): self
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
}
