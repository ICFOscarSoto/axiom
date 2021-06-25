<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPStores;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPInfoStocksRepository")
 */
class ERPInfoStocks
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Store;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $PendingToReceive;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $PendingToServe;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $MinimumAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $MaximunQuantity;

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

    public function getStore(): ?ERPStores
    {
        return $this->Store;
    }

    public function setStore(?ERPStores $Store): self
    {
        $this->Store = $Store;

        return $this;
    }

    public function getPendingToReceive(): ?float
    {
        return $this->PendingToReceive;
    }

    public function setPendingToReceive(?float $PendingToReceive): self
    {
        $this->PendingToReceive = $PendingToReceive;

        return $this;
    }

    public function getPendingToServe(): ?float
    {
        return $this->PendingToServe;
    }

    public function setPendingToServe(?float $PendingToServe): self
    {
        $this->PendingToServe = $PendingToServe;

        return $this;
    }

    public function getMinimumAmount(): ?float
    {
        return $this->MinimumAmount;
    }

    public function setMinimumAmount(?float $MinimumAmount): self
    {
        $this->MinimumAmount = $MinimumAmount;

        return $this;
    }

    public function getMaximunQuantity(): ?float
    {
        return $this->MaximunQuantity;
    }

    public function setMaximunQuantity(?float $MaximunQuantity): self
    {
        $this->MaximunQuantity = $MaximunQuantity;

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
}
