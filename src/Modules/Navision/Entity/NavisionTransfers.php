<?php

namespace App\Modules\Navision\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\ERP\Entity\ERPProducts;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Navision\Repository\NavisionTransfersRepository")
 */
class NavisionTransfers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datesend;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     */
    private $originstore;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     */
    private $destinationstore;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDatesend(): ?\DateTimeInterface
    {
        return $this->datesend;
    }

    public function setDatesend(\DateTimeInterface $datesend): self
    {
        $this->datesend = $datesend;

        return $this;
    }

    public function getOriginstore(): ?ERPStores
    {
        return $this->originstore;
    }

    public function setOriginstore(?ERPStores $originstore): self
    {
        $this->originstore = $originstore;

        return $this;
    }

    public function getDestinationstore(): ?ERPStores
    {
        return $this->destinationstore;
    }

    public function setDestinationstore(?ERPStores $destinationstore): self
    {
        $this->destinationstore = $destinationstore;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
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

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

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
