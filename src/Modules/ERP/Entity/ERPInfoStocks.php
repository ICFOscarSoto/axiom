<?php

namespace App\Modules\ERP\Entity;

use App\Modules\Globale\Entity\GlobaleUsers;
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
    private $store;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pendingToReceive;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pendingToServe;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minimumQuantity;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maximunQuantity;

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
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPProductsVariants")
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

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
        return $this->store;
    }

    public function setStore(?ERPStores $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function getPendingToReceive(): ?float
    {
        return $this->pendingToReceive;
    }

    public function setPendingToReceive(?float $pendingToReceive): self
    {
        $this->pendingToReceive = $pendingToReceive;

        return $this;
    }

    public function getPendingToServe(): ?float
    {
        return $this->pendingToServe;
    }

    public function setPendingToServe(?float $pendingToServe): self
    {
        $this->pendingToServe = $pendingToServe;

        return $this;
    }

    public function getMinimumQuantity(): ?float
    {
        return $this->minimumQuantity;
    }

    public function setMinimumQuantity(?float $minimumQuantity): self
    {
        $this->minimumQuantity = $minimumQuantity;

        return $this;
    }

    public function getMaximunQuantity(): ?float
    {
        return $this->maximunQuantity;
    }

    public function setMaximunQuantity(?float $maximunQuantity): self
    {
        $this->maximunQuantity = $maximunQuantity;

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

    public function getProductvariant(): ?ERPProductsVariants
    {
        return $this->productvariant;
    }

    public function setProductvariant(?ERPProductsVariants $productvariant): self
    {
        $this->productvariant = $productvariant;

        return $this;
    }

    public function getAuthor(): ?GlobaleUsers
    {
        return $this->author;
    }

    public function setAuthor(?GlobaleUsers $author): self
    {
        $this->author = $author;

        return $this;
    }
}
