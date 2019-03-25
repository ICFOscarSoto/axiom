<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProductVariants;
use \APP\Modules\ERP\Entity\ERPVariants;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPProductVariantsCombinationsRepository")
 */
class ERPProductVariantsCombinations
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProductVariants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="\APP\Modules\ERP\Entity\ERPVariants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $variant;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

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

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductvariant(): ?ERPProductVariants
    {
        return $this->productvariant;
    }

    public function setProductvariant(?ERPProductVariants $productvariant): self
    {
        $this->productvariant = $productvariant;

        return $this;
    }

    public function getVariant(): ?ERPVariants
    {
        return $this->variant;
    }

    public function setVariant(?ERPVariants $variant): self
    {
        $this->variant = $variant;

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
}
