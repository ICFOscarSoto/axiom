<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\Carrier\Entity\CarrierCarriers;
use \App\Modules\Carrier\Entity\CarrierShippingConditions;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPCustomerOrdersDataRepository")
 */
class ERPCustomerOrdersData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $requiredordernumber;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $invoicefordeliverynote;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $pricesdeliverynote;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $partialshipping;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $authorizationcontrol;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Carrier\Entity\CarrierCarriers")
     */
    private $carrier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Carrier\Entity\CarrierShippingConditions")
     */
    private $shippingconditions;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

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

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRequiredordernumber(): ?bool
    {
        return $this->requiredordernumber;
    }

    public function setRequiredordernumber(?bool $requiredordernumber): self
    {
        $this->requiredordernumber = $requiredordernumber;

        return $this;
    }

    public function getInvoicefordeliverynote(): ?bool
    {
        return $this->invoicefordeliverynote;
    }

    public function setInvoicefordeliverynote(?bool $invoicefordeliverynote): self
    {
        $this->invoicefordeliverynote = $invoicefordeliverynote;

        return $this;
    }

    public function getPricesdeliverynote(): ?bool
    {
        return $this->pricesdeliverynote;
    }

    public function setPricesdeliverynote(?bool $pricesdeliverynote): self
    {
        $this->pricesdeliverynote = $pricesdeliverynote;

        return $this;
    }

    public function getPartialshipping(): ?bool
    {
        return $this->partialshipping;
    }

    public function setPartialshipping(?bool $partialshipping): self
    {
        $this->partialshipping = $partialshipping;

        return $this;
    }

    public function getAuthorizationcontrol(): ?bool
    {
        return $this->authorizationcontrol;
    }

    public function setAuthorizationcontrol(?bool $authorizationcontrol): self
    {
        $this->authorizationcontrol = $authorizationcontrol;

        return $this;
    }

    public function getCarrier(): ?CarrierCarriers
    {
        return $this->carrier;
    }

    public function setCarrier(?CarrierCarriers $carrier): self
    {
        $this->carrier = $carrier;

        return $this;
    }

    public function getShippingconditions(): ?CarrierShippingConditions
    {
        return $this->shippingconditions;
    }

    public function setShippingconditions(?CarrierShippingConditions $shippingconditions): self
    {
        $this->shippingconditions = $shippingconditions;

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
}
