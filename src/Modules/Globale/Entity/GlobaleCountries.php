<?php

namespace App\Modules\Globale\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleCountriesRepository")
 */
class GlobaleCountries
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $isoname;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $alfa2;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $alfa3;

    /**
     * @ORM\Column(type="integer")
     */
    private $isonumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies", mappedBy="country")
     */
    private $companies;

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
     * @ORM\Column(type="boolean")
     */
    private $active;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

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

    public function getIsoname(): ?string
    {
        return $this->isoname;
    }

    public function setIsoname(string $isoname): self
    {
        $this->isoname = $isoname;

        return $this;
    }

    public function getAlfa2(): ?string
    {
        return $this->alfa2;
    }

    public function setAlfa2(string $alfa2): self
    {
        $this->alfa2 = $alfa2;

        return $this;
    }

    public function getAlfa3(): ?string
    {
        return $this->alfa3;
    }

    public function setAlfa3(string $alfa3): self
    {
        $this->alfa3 = $alfa3;

        return $this;
    }

    public function getIsonumber(): ?int
    {
        return $this->isonumber;
    }

    public function setIsonumber(int $isonumber): self
    {
        $this->isonumber = $isonumber;

        return $this;
    }

    /**
     * @return Collection|Companies[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(GlobaleCompanies $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->setCountry($this);
        }

        return $this;
    }

    public function removeCompany(GlobaleCompanies $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            // set the owning side to null (unless already changed)
            if ($company->getCountry() === $this) {
                $company->setCountry(null);
            }
        }

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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
