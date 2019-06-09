<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleQuotationGroupsRepository")
 */
class GlobaleQuotationGroups
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $codgroup;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minsalary;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxsalary;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $periodsalary;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodgroup(): ?string
    {
        return $this->codgroup;
    }

    public function setCodgroup(string $codgroup): self
    {
        $this->codgroup = $codgroup;

        return $this;
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

    public function getMinsalary(): ?float
    {
        return $this->minsalary;
    }

    public function setMinsalary(?float $minsalary): self
    {
        $this->minsalary = $minsalary;

        return $this;
    }

    public function getMaxsalary(): ?float
    {
        return $this->maxsalary;
    }

    public function setMaxsalary(?float $maxsalary): self
    {
        $this->maxsalary = $maxsalary;

        return $this;
    }

    public function getPeriodsalary(): ?string
    {
        return $this->periodsalary;
    }

    public function setPeriodsalary(?string $periodsalary): self
    {
        $this->periodsalary = $periodsalary;

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
