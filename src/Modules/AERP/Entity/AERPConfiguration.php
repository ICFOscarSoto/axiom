<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleTaxes;
use \App\Modules\AERP\Entity\AERPPaymentMethods;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\AERP\Entity\AERPConfigurationRepository")
 */
class AERPConfiguration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $margincontrol;

    /**
     * @ORM\Column(type="float")
     */
    private $margin;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleTaxes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $defaulttax;

    /**
     * @ORM\Column(type="boolean")
     */
    private $irpf;

    /**
     * @ORM\Column(type="float")
     */
    private $defaultirpf;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPPaymentMethods")
     */
    private $defaultpaymentmethod;

    /**
     * @ORM\Column(type="integer")
     */
    private $budgetexpiration;

    /**
     * @ORM\Column(type="smallint")
     */
    private $budgetexpirationtype;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMargincontrol(): ?bool
    {
        return $this->margincontrol;
    }

    public function setMargincontrol(bool $margincontrol): self
    {
        $this->margincontrol = $margincontrol;

        return $this;
    }

    public function getMargin(): ?float
    {
        return $this->margin;
    }

    public function setMargin(float $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    public function getDefaulttax(): ?GlobaleTaxes
    {
        return $this->defaulttax;
    }

    public function setDefaulttax(?GlobaleTaxes $defaulttax): self
    {
        $this->defaulttax = $defaulttax;

        return $this;
    }

    public function getIrpf(): ?bool
    {
        return $this->irpf;
    }

    public function setIrpf(bool $irpf): self
    {
        $this->irpf = $irpf;

        return $this;
    }

    public function getDefaultirpf(): ?float
    {
        return $this->defaultirpf;
    }

    public function setDefaultirpf(float $defaultirpf): self
    {
        $this->defaultirpf = $defaultirpf;

        return $this;
    }

    public function getDefaultpaymentmethod(): ?AERPPaymentMethods
    {
        return $this->defaultpaymentmethod;
    }

    public function setDefaultpaymentmethod(?AERPPaymentMethods $defaultpaymentmethod): self
    {
        $this->defaultpaymentmethod = $defaultpaymentmethod;

        return $this;
    }

    public function getBudgetexpiration(): ?int
    {
        return $this->budgetexpiration;
    }

    public function setBudgetexpiration(int $budgetexpiration): self
    {
        $this->budgetexpiration = $budgetexpiration;

        return $this;
    }

    public function getBudgetexpirationtype(): ?int
    {
        return $this->budgetexpirationtype;
    }

    public function setBudgetexpirationtype(int $budgetexpirationtype): self
    {
        $this->budgetexpirationtype = $budgetexpirationtype;

        return $this;
    }
}
