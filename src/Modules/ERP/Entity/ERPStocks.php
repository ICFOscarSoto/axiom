<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPStoreLocations;
use \App\Modules\ERP\Entity\ERPProductsVariants;
use \App\Modules\ERP\Entity\ERPVariants;
use \App\Modules\Globale\Entity\GlobaleUsers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStocksRepository")
 */
class ERPStocks
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastinventorydate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pendingserve=0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pendingreceive=0;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minstock;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxstock;

    /**
     * @ORM\Column(type="float")
     */
    private $quantity;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreLocations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $storelocation;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProductsVariants")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLastinventorydate(): ?\DateTimeInterface
    {
        return $this->lastinventorydate;
    }

    public function setLastinventorydate(?\DateTimeInterface $lastinventorydate): self
    {
        $this->lastinventorydate = $lastinventorydate;

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

    public function getPendingreceive(): ?float
    {
        return $this->pendingreceive;
    }

    public function setPendingreceive(?float $pendingreceive): self
    {
        $this->pendingreceive = $pendingreceive;

        return $this;
    }

    public function getMinstock(): ?float
    {
        return $this->minstock;
    }

    public function setMinstock(?float $minstock): self
    {
        $this->minstock = $minstock;

        return $this;
    }

    public function getMaxstock(): ?float
    {
        return $this->maxstock;
    }

    public function setMaxstock(?float $maxstock): self
    {
        $this->maxstock = $maxstock;

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

    public function getStorelocation(): ?ERPStoreLocations
    {
        return $this->storelocation;
    }

    public function setStorelocation(?ERPStoreLocations $storelocation): self
    {
        $this->storelocation = $storelocation;

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
