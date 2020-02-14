<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPCustomers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPBankAccountsRepository")
 */
class ERPBankAccounts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=24)
     */
    private $iban;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    private $swiftcode;

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
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $socialname;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sepacore;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sepab2b;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $creditoridentifier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     */
    private $supplier;

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

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getSwiftcode(): ?string
    {
        return $this->swiftcode;
    }

    public function setSwiftcode(string $swiftcode): self
    {
        $this->swiftcode = $swiftcode;

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

    public function getSocialname(): ?string
    {
        return $this->socialname;
    }

    public function setSocialname(?string $socialname): self
    {
        $this->socialname = $socialname;

        return $this;
    }

    public function getSepacore(): ?bool
    {
        return $this->sepacore;
    }

    public function setSepacore(?bool $sepacore): self
    {
        $this->sepacore = $sepacore;

        return $this;
    }

    public function getSepab2b(): ?bool
    {
        return $this->sepab2b;
    }

    public function setSepab2b(?bool $sepab2b): self
    {
        $this->sepab2b = $sepab2b;

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

    public function getCreditoridentifier(): ?string
    {
        return $this->creditoridentifier;
    }

    public function setCreditoridentifier(?string $creditoridentifier): self
    {
        $this->creditoridentifier = $creditoridentifier;

        return $this;
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

    public function getSupplier(): ?ERPCustomers
    {
        return $this->supplier;
    }

    public function setSupplier(?ERPCustomers $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }
}
