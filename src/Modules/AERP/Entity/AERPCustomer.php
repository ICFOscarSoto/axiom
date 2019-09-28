<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\AERP\Entity\AERPContact;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\AERP\Repository\AERPCustomerRepository")
 */
class AERPCustomer
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $vat;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPContact")
     */
    private $shippcontact;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPContact")
     */
    private $invoicecontact;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxrisk;

    /**
     * @ORM\Column(type="float")
     */
    private $risk;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="boolean")
     */
    private $taxexempt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $surcharge;

    /**
     * @ORM\Column(type="string", length=175)
     */
    private $web;

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

    public function getAgent(): ?GlobaleUsers
    {
        return $this->agent;
    }

    public function setAgent(?GlobaleUsers $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setVat(string $vat): self
    {
        $this->vat = $vat;

        return $this;
    }

    public function getShippcontact(): ?AERPContact
    {
        return $this->shippcontact;
    }

    public function setShippcontact(?AERPContact $shippcontact): self
    {
        $this->shippcontact = $shippcontact;

        return $this;
    }

    public function getInvoicecontact(): ?AERPContact
    {
        return $this->invoicecontact;
    }

    public function setInvoicecontact(?AERPContact $invoicecontact): self
    {
        $this->invoicecontact = $invoicecontact;

        return $this;
    }

    public function getMaxrisk(): ?float
    {
        return $this->maxrisk;
    }

    public function setMaxrisk(?float $maxrisk): self
    {
        $this->maxrisk = $maxrisk;

        return $this;
    }

    public function getRisk(): ?float
    {
        return $this->risk;
    }

    public function setRisk(float $risk): self
    {
        $this->risk = $risk;

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

    public function getAuthor(): ?GlobaleUsers
    {
        return $this->author;
    }

    public function setAuthor(?GlobaleUsers $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getTaxexempt(): ?bool
    {
        return $this->taxexempt;
    }

    public function setTaxexempt(bool $taxexempt): self
    {
        $this->taxexempt = $taxexempt;

        return $this;
    }

    public function getSurcharge(): ?bool
    {
        return $this->surcharge;
    }

    public function setSurcharge(bool $surcharge): self
    {
        $this->surcharge = $surcharge;

        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function setWeb(string $web): self
    {
        $this->web = $web;

        return $this;
    }


}
