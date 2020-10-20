<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPWebProductsRepository")
 */
class ERPWebProducts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

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
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $metatitle;

    /**
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $metadescription;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $additionalcost;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minquantityofsaleweb;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $equivalence;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $measurementunityofequivalence;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?ERPProducts
    {
        return $this->product;
    }

    public function setProduct(ERPProducts $product): self
    {
        $this->product = $product;

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

    public function getMetatitle(): ?string
    {
        return $this->metatitle;
    }

    public function setMetatitle(?string $metatitle): self
    {
        $this->metatitle = $metatitle;

        return $this;
    }

    public function getMetadescription(): ?string
    {
        return $this->metadescription;
    }

    public function setMetadescription(?string $metadescription): self
    {
        $this->metadescription = $metadescription;

        return $this;
    }

    public function getAdditionalcost(): ?float
    {
        return $this->additionalcost;
    }

    public function setAdditionalcost(?float $additionalcost): self
    {
        $this->additionalcost = $additionalcost;

        return $this;
    }

    public function getMinquantityofsaleweb(): ?int
    {
        return $this->minquantityofsaleweb;
    }

    public function setMinquantityofsaleweb(?int $minquantityofsaleweb): self
    {
        $this->minquantityofsaleweb = $minquantityofsaleweb;

        return $this;
    }

    public function getEquivalence(): ?float
    {
        return $this->equivalence;
    }

    public function setEquivalence(?float $equivalence): self
    {
        $this->equivalence = $equivalence;

        return $this;
    }

    public function getMeasurementunityofequivalence(): ?string
    {
        return $this->measurementunityofequivalence;
    }

    public function setMeasurementunityofequivalence(?string $measurementunityofequivalence): self
    {
        $this->measurementunityofequivalence = $measurementunityofequivalence;

        return $this;
    }


}
