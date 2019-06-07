<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPDepartments;
use \App\Modules\ERP\Entity\ERPSuppliers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPContactsRepository")
 */
class ERPContacts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPDepartments")
     */
    private $department;

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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $purchaseorder;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $saleorder;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDepartment(): ?ERPDepartments
    {
        return $this->department;
    }

    public function setDepartment(?ERPDepartments $department): self
    {
        $this->department = $department;

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

    public function getPurchaseorder(): ?bool
    {
        return $this->purchaseorder;
    }

    public function setPurchaseorder(?bool $purchaseorder): self
    {
        $this->purchaseorder = $purchaseorder;

        return $this;
    }

    public function getSaleorder(): ?bool
    {
        return $this->saleorder;
    }

    public function setSaleorder(?bool $saleorder): self
    {
        $this->saleorder = $saleorder;

        return $this;
    }

    public function getInvoice(): ?bool
    {
        return $this->invoice;
    }

    public function setInvoice(?bool $invoice): self
    {
        $this->invoice = $invoice;

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
