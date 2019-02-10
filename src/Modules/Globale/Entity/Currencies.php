<?php

namespace App\Modules\Globale\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\CurrenciesRepository")
 */
class Currencies
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $isocode;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $numcode;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $charcode;

    /**
     * @ORM\Column(type="integer")
     */
    private $decimals;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\Companies", mappedBy="currency")
     */
    private $companies;

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

    public function getIsocode(): ?string
    {
        return $this->isocode;
    }

    public function setIsocode(string $isocode): self
    {
        $this->isocode = $isocode;

        return $this;
    }

    public function getNumcode(): ?string
    {
        return $this->numcode;
    }

    public function setNumcode(string $numcode): self
    {
        $this->numcode = $numcode;

        return $this;
    }

    public function getCharcode(): ?string
    {
        return $this->charcode;
    }

    public function setCharcode(string $charcode): self
    {
        $this->charcode = $charcode;

        return $this;
    }

    public function getDecimals(): ?int
    {
        return $this->decimals;
    }

    public function setDecimals(int $decimals): self
    {
        $this->decimals = $decimals;

        return $this;
    }

    /**
     * @return Collection|Companies[]
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Companies $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies[] = $company;
            $company->setCurrency($this);
        }

        return $this;
    }

    public function removeCompany(Companies $company): self
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            // set the owning side to null (unless already changed)
            if ($company->getCurrency() === $this) {
                $company->setCurrency(null);
            }
        }

        return $this;
    }

    public function encodeJson (){
      $vars = get_object_vars ( $this );
      foreach( $vars as $key=>$var){
          if(is_object($var)){
            if(get_class($var)=="DateTime"){
                $tempArray[$key."-date"] = $var->format('d/m/Y');
                $tempArray[$key."-time"] = $var->format('H:i:s');;
            }else{
              if(method_exists($var,"getId")){
                $tempArray[$key] = $var->getId();
              }
            }
          }else{
            $tempArray[$key] = $var;
          }
      }
      return $tempArray;
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
