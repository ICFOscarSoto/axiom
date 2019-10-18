<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPAttributesValues;
use \App\Modules\ERP\Entity\ERPAttributeNames;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPProductsAttributesRepository")
 */
class ERPProductsAttributes
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPAttributesValues")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $attributevalue;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPAttributeNames")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $attributename;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted=0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateupd;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAttributevalue(): ?ERPAttributesValues
    {
        return $this->attributevalue;
    }

    public function setAttributevalue(?ERPAttributesValues $attributevalue): self
    {
        $this->attributevalue = $attributevalue;

        return $this;
    }

    public function getAttributename(): ?ERPAttributeNames
    {
        return $this->attributename;
    }

    public function setAttributename(?ERPAttributeNames $attributename): self
    {
        $this->attributename = $attributename;

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
