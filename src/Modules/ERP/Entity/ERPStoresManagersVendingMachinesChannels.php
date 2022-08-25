<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStoresManagersVendingMachinesChannelsRepository")
 */
class ERPStoresManagersVendingMachinesChannels
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoresManagersVendingMachines")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vendingmachine;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $row;

    /**
     * @ORM\Column(type="smallint")
     */
    private $col;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $minquantity=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxquantity;

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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $multiplier=1;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $expirydate;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $productcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $productname;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $channel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gaps;

    public function getLacks($doctrine): ?int
    {
            $lacks=$this->minquantity-$this->quantity;
            if ($lacks>0) return $lacks;
            return 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendingmachine(): ?ERPStoresManagersVendingMachines
    {
        return $this->vendingmachine;
    }

    public function setVendingmachine(?ERPStoresManagersVendingMachines $vendingmachine): self
    {
        $this->vendingmachine = $vendingmachine;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(int $row): self
    {
        $this->row = $row;

        return $this;
    }

    public function getCol(): ?int
    {
        return $this->col;
    }

    public function setCol(int $col): self
    {
        $this->col = $col;

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

    public function getMinquantity(): ?int
    {
        return $this->minquantity;
    }

    public function setMinquantity(int $minquantity): self
    {
        $this->minquantity = $minquantity;

        return $this;
    }

    public function getMaxquantity(): ?int
    {
        return $this->maxquantity;
    }

    public function setMaxquantity(int $maxquantity): self
    {
        $this->maxquantity = $maxquantity;

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

    public function getMultiplier(): ?int
    {
        return $this->multiplier;
    }

    public function setMultiplier(?int $multiplier): self
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    public function getExpirydate(): ?\DateTimeInterface
    {
        return $this->expirydate;
    }

    public function setExpirydate(?\DateTimeInterface $expirydate): self
    {
        $this->expirydate = $expirydate;

        return $this;
    }

    public function getProductcode(): ?string
    {
        return $this->productcode;
    }

    public function setProductcode(string $productcode): self
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

    public function preProccess($kernel, $doctrine, $user, $params, $oldobj)
    {
      if($this->product!=null){
        $this->productcode=$this->product->getCode();
        $this->productname=$this->product->getName();
      }
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getGaps(): ?int
    {
        return $this->gaps;
    }

    public function setGaps(?int $gaps): self
    {
        $this->gaps = $gaps;

        return $this;
    }

}
