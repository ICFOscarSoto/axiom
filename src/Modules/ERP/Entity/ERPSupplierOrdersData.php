<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\Carrier\Entity\CarrierCarriers;
use \App\Modules\Carrier\Entity\CarrierShippingConditions;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSupplierOrdersDataRepository")
 */
class ERPSupplierOrdersData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $freeshipping;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minorder;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $estimateddelivery;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $averagedelivery;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cancelremains;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $dropshipping;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $allowpicking;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Carrier\Entity\CarrierCarriers")
     */
    private $carrier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Carrier\Entity\CarrierShippingConditions")
     */
    private $shippingconditions;

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

    public function getSupplier(): ?ERPSuppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?ERPSuppliers $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getFreeshipping(): ?float
    {
        return $this->freeshipping;
    }

    public function setFreeshipping(?float $freeshipping): self
    {
        $this->freeshipping = $freeshipping;

        return $this;
    }

    public function getMinorder(): ?float
    {
        return $this->minorder;
    }

    public function setMinorder(?float $minorder): self
    {
        $this->minorder = $minorder;

        return $this;
    }

    public function getEstimateddelivery(): ?int
    {
        return $this->estimateddelivery;
    }

    public function setEstimateddelivery(?int $estimateddelivery): self
    {
        $this->estimateddelivery = $estimateddelivery;

        return $this;
    }

    public function getAveragedelivery(): ?int
    {
        return $this->averagedelivery;
    }

    public function setAveragedelivery(?int $averagedelivery): self
    {
        $this->averagedelivery = $averagedelivery;

        return $this;
    }

    public function getCancelremains(): ?bool
    {
        return $this->cancelremains;
    }

    public function setCancelremains(?bool $cancelremains): self
    {
        $this->cancelremains = $cancelremains;

        return $this;
    }

    public function getDropshipping(): ?bool
    {
        return $this->dropshipping;
    }

    public function setDropshipping(?bool $dropshipping): self
    {
        $this->dropshipping = $dropshipping;

        return $this;
    }

    public function getAllowpicking(): ?bool
    {
        return $this->allowpicking;
    }

    public function setAllowpicking(?bool $allowpicking): self
    {
        $this->allowpicking = $allowpicking;

        return $this;
    }

    public function getCarrier(): ?CarrierCarriers
    {
        return $this->carrier;
    }

    public function setCarrier(?CarrierCarriers $carrier): self
    {
        $this->carrier = $carrier;

        return $this;
    }

    public function getShippingconditions(): ?CarrierShippingConditions
    {
        return $this->shippingconditions;
    }

    public function setShippingconditions(?CarrierShippingConditions $shippingconditions): self
    {
        $this->shippingconditions = $shippingconditions;

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
