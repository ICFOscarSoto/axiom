<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPInventory;
use \App\Modules\ERP\Entity\ERPProductsVariants;
use \App\Modules\ERP\Entity\ERPStoreLocations;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPInventoryLinesRepository")
 */
class ERPInventoryLines
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPInventory")
     * @ORM\JoinColumn(nullable=false)
     */
    private $inventory;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProductsVariants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreLocations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="float")
     */
    private $quantityconfirmed;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $stockold;

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

    public function getInventory(): ?ERPInventory
    {
        return $this->inventory;
    }

    public function setInventory(?ERPInventory $inventory): self
    {
        $this->inventory = $inventory;

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

    public function getLocation(): ?ERPStoreLocations
    {
        return $this->location;
    }

    public function setLocation(?ERPStoreLocations $location): self
    {
        $this->location = $location;

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

    public function getQuantityconfirmed(): ?float
    {
        return $this->quantityconfirmed;
    }

    public function setQuantityconfirmed(float $quantityconfirmed): self
    {
        $this->quantityconfirmed = $quantityconfirmed;

        return $this;
    }

    public function getStockold(): ?float
    {
        return $this->stockold;
    }

    public function setStockold(float $stockold): self
    {
        $this->stockold = $stockold;

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
