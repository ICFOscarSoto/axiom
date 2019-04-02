<?php

namespace App\Modules\ERP\Entity;

//use Doctrine\Common\Collections\ArrayCollection;
//use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPEntities;
//use \App\Modules\Carrier\Entity\CarrierCarriers;
//use \App\Modules\ERP\Entity\ERPAddresses;
//use \App\Modules\ERP\Entity\ERPEntity;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPCustomersRepository")
 */
class ERPCustomers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $maxcredit;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $blockcredit;

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
     * @ORM\Column(type="float", nullable=true)
     */
    private $mininvoice;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $allowlinediscount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $additionaldiscount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $requireordernumber;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Carrier\Entity\CarrierCarriers")
     */
    //private $carrier;

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
     * @ORM\OneToMany(targetEntity="\App\Modules\ERP\Entity\ERPAddresses", mappedBy="customers", orphanRemoval=true)
     */
    //private $shippingaddress;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoiceday;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\OneToOne(targetEntity="\App\Modules\ERP\Entity\ERPEntities", fetch="EAGER", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    /*public function __construct()
    {
        $this->shippingaddress = new ArrayCollection();
    } */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxcredit(): ?float
    {
        return $this->maxcredit;
    }

    public function setMaxcredit(?float $maxcredit): self
    {
        $this->maxcredit = $maxcredit;

        return $this;
    }

    public function getBlockcredit(): ?bool
    {
        return $this->blockcredit;
    }

    public function setBlockcredit(?bool $blockcredit): self
    {
        $this->blockcredit = $blockcredit;

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

    public function getMininvoice(): ?float
    {
        return $this->mininvoice;
    }

    public function setMininvoice(?float $mininvoice): self
    {
        $this->mininvoice = $mininvoice;

        return $this;
    }

    public function getAllowlinediscount(): ?bool
    {
        return $this->allowlinediscount;
    }

    public function setAllowlinediscount(?bool $allowlinediscount): self
    {
        $this->allowlinediscount = $allowlinediscount;

        return $this;
    }

    public function getAdditionaldiscount(): ?float
    {
        return $this->additionaldiscount;
    }

    public function setAdditionaldiscount(?float $additionaldiscount): self
    {
        $this->additionaldiscount = $additionaldiscount;

        return $this;
    }

    public function getRequireordernumber(): ?bool
    {
        return $this->requireordernumber;
    }

    public function setRequireordernumber(?bool $requireordernumber): self
    {
        $this->requireordernumber = $requireordernumber;

        return $this;
    }

  /*  public function getCarrier(): ?CarrierCarriers
    {
        return $this->carrier;
    }

    public function setCarrier(?CarrierCarriers $carrier): self
    {
        $this->carrier = $carrier;

        return $this;
    }
 */
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

    /**
     * @return Collection|ERPAddresses[]
     */
    /*public function getShippingaddress(): Collection
    {
        return $this->shippingaddress;
    }

    public function addShippingaddress(ERPAddresses $shippingaddress): self
    {
        if (!$this->shippingaddress->contains($shippingaddress)) {
            $this->shippingaddress[] = $shippingaddress;
            $shippingaddress->setCustomers($this);
        }

        return $this;
    }

    public function removeShippingaddress(ERPAddresses $shippingaddress): self
    {
        if ($this->shippingaddress->contains($shippingaddress)) {
            $this->shippingaddress->removeElement($shippingaddress);
            // set the owning side to null (unless already changed)
            if ($shippingaddress->getCustomers() === $this) {
                $shippingaddress->setCustomers(null);
            }
        }

        return $this;
    }
    */

    public function getInvoiceday(): ?int
    {
        return $this->invoiceday;
    }

    public function setInvoiceday(?int $invoiceday): self
    {
        $this->invoiceday = $invoiceday;

        return $this;
    }

    public function getEntity(): ?ERPEntities
    {
        return $this->entity;
    }

    public function setEntity(ERPEntities $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
