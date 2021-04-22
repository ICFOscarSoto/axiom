<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPVariantsValues;
use \App\Modules\ERP\Entity\ERPPurchasesDemandsReasons;
use \App\Modules\ERP\Entity\ERPSuppliers;
/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPPurchasesDemandsRepository")
 */
class ERPPurchasesDemands
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPurchasesDemandsReasons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $reason;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $observations;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPVariantsValues")
     */
    private $variant;

    /**
     * @ORM\Column(type="integer")
     */
    private $linenum;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgent(): ?GlobaleUsers
    {
        return $this->agent;
    }

    public function setAgent(?GlobaleUsers $agent): self
    {
        $this->agent = $agent;

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

    public function getReason(): ?ERPPurchasesDemandsReasons
    {
        return $this->reason;
    }

    public function setReason(?ERPPurchasesDemandsReasons $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

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

    public function getVariant(): ?ERPVariantsValues
    {
        return $this->variant;
    }

    public function setVariant(?ERPVariantsValues $variant): self
    {
        $this->variant = $variant;

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

    public function getSupplier(): ?ERPSuppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?ERPSuppliers $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

}
