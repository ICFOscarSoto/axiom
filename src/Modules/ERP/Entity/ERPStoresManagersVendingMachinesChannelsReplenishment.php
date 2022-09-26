<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannelsReplenishmentRepository")
 */
class ERPStoresManagersVendingMachinesChannelsReplenishment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels")
     * @ORM\JoinColumn(nullable=false)
     */
    private $channel;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

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
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPProducts")
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $productcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $productname;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?ERPStoresManagersVendingMachinesChannels
    {
        return $this->channel;
    }

    public function setChannel(?ERPStoresManagersVendingMachinesChannels $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getProduct(): ?ERPProducts
    {
        return $this->product;
    }

    public function setProduct(?ERPProducts $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getProductcode(): ?string
    {
        return $this->productcode;
    }

    public function setProductcode(?string $productcode): self
    {
        $this->productcode = $productcode;

        return $this;
    }

    public function getProductname(): ?string
    {
        return $this->productname;
    }

    public function setProductname(?string $productname): self
    {
        $this->productname = $productname;

        return $this;
    }
}
