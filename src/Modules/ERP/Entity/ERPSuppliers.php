<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPPaymentMethods;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSuppliersRepository")
 */
class ERPSuppliers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minorder;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $freeshipping;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $estimateddelivery;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $averagedelivery;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cancelremains;

    /**
     * @ORM\Column(type="boolean")
     */
    private $dropshipping;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allowpicking;

    /**
     * @ORM\Column(type="boolean")
     */
    private $creditor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoiceday;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentMethods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $paymentmethod;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFreeshipping(): ?float
    {
        return $this->freeshipping;
    }

    public function setFreeshipping(?float $freeshipping): self
    {
        $this->freeshipping = $freeshipping;

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

    public function setCancelremains(bool $cancelremains): self
    {
        $this->cancelremains = $cancelremains;

        return $this;
    }

    public function getDropshipping(): ?bool
    {
        return $this->dropshipping;
    }

    public function setDropshipping(bool $dropshipping): self
    {
        $this->dropshipping = $dropshipping;

        return $this;
    }

    public function getAllowpicking(): ?bool
    {
        return $this->allowpicking;
    }

    public function setAllowpicking(bool $allowpicking): self
    {
        $this->allowpicking = $allowpicking;

        return $this;
    }

    public function getCreditor(): ?bool
    {
        return $this->creditor;
    }

    public function setCreditor(bool $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getInvoiceday(): ?int
    {
        return $this->invoiceday;
    }

    public function setInvoiceday(?int $invoiceday): self
    {
        $this->invoiceday = $invoiceday;

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

    public function getPaymentmethod(): ?ERPPaymentMethods
    {
        return $this->paymentmethod;
    }

    public function setPaymentmethod(?ERPPaymentMethods $paymentmethod): self
    {
        $this->paymentmethod = $paymentmethod;

        return $this;
    }

}
