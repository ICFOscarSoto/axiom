<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\ERP\Entity\ERPCustomerGroups;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPCustomerCommercialTermsRepository")
 */
class ERPCustomerCommercialTerms
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomerGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customergroup;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $allowlinediscount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $additionaldiscount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $insured;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $supplement;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $cescecode;

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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $surchage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCustomergroup(): ?ERPCustomerGroups
    {
        return $this->customergroup;
    }

    public function setCustomergroup(?ERPCustomerGroups $customergroup): self
    {
        $this->customergroup = $customergroup;

        return $this;
    }

    public function getAllowlinediscount(): ?bool
    {
        return $this->allowlinediscount;
    }

    public function setAllowlinediscount(?bool $allowlinediscount): self
    {
        $this->allowlinediscount = $allowlinediscount;

        return $this;
    }

    public function getAdditionaldiscount(): ?float
    {
        return $this->additionaldiscount;
    }

    public function setAdditionaldiscount(float $additionaldiscount): self
    {
        $this->additionaldiscount = $additionaldiscount;

        return $this;
    }

    public function getInsured(): ?bool
    {
        return $this->insured;
    }

    public function setInsured(?bool $insured): self
    {
        $this->insured = $insured;

        return $this;
    }

    public function getSupplement(): ?string
    {
        return $this->supplement;
    }

    public function setSupplement(?string $supplement): self
    {
        $this->supplement = $supplement;

        return $this;
    }

    public function getCescecode(): ?string
    {
        return $this->cescecode;
    }

    public function setCescecode(?string $cescecode): self
    {
        $this->cescecode = $cescecode;

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

    public function getSurchage(): ?bool
    {
        return $this->surchage;
    }

    public function setSurchage(?bool $surchage): self
    {
        $this->surchage = $surchage;

        return $this;
    }
}
