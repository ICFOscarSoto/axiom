<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPStoreLocations;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPTypesMovements;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStocksHistoryRepository")
 */
class ERPStocksHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreLocations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;


    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $previousqty;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $newqty;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPProductsVariants")
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPTypesMovements")
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $numOperation;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     */
    private $company;

    public function getId(): ?int
    {
        return $this->id;
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


    public function getPreviousqty(): ?float
    {
        return $this->previousqty;
    }

    public function setPreviousqty(float $previousqty): self
    {
        $this->previousqty = $previousqty;

        return $this;
    }

    public function getNewqty(): ?float
    {
        return $this->newqty;
    }

    public function setNewqty(float $newqty): self
    {
        $this->newqty = $newqty;

        return $this;
    }

    public function getUser(): ?GlobaleUsers
    {
        return $this->user;
    }

    public function setUser(?GlobaleUsers $user): self
    {
        $this->user = $user;

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

    public function getProductvariant(): ?ERPProductsVariants
    {
        return $this->productvariant;
    }

    public function setProductvariant(?ERPProductsVariants $productvariant): self
    {
        $this->productvariant = $productvariant;

        return $this;
    }

    public function getType(): ?ERPTypesMovements
    {
        return $this->type;
    }

    public function setType(?ERPTypesMovements $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getNumOperation(): ?string
    {
        return $this->numOperation;
    }

    public function setNumOperation(?string $numOperation): self
    {
        $this->numOperation = $numOperation;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): self
    {
        $this->quantity = $quantity;

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
}
