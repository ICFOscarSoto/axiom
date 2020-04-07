<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPTransfersHeaderRepository")
 */
class ERPTransfersHeader
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
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $OriginStore;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStores")
     * @ORM\JoinColumn(nullable=false)
     */
    private $DestinationStore;

    /**
     * @ORM\Column(type="datetime")
     */
    private $SendDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $EstimatedDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $state;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getOriginStore(): ?ERPStores
    {
        return $this->OriginStore;
    }

    public function setOriginStore(?ERPStores $OriginStore): self
    {
        $this->OriginStore = $OriginStore;

        return $this;
    }

    public function getDestinationStore(): ?ERPStores
    {
        return $this->DestinationStore;
    }

    public function setDestinationStore(?ERPStores $DestinationStore): self
    {
        $this->DestinationStore = $DestinationStore;

        return $this;
    }

    public function getSendDate(): ?\DateTimeInterface
    {
        return $this->SendDate;
    }

    public function setSendDate(\DateTimeInterface $SendDate): self
    {
        $this->SendDate = $SendDate;

        return $this;
    }

    public function getEstimatedDate(): ?\DateTimeInterface
    {
        return $this->EstimatedDate;
    }

    public function setEstimatedDate(\DateTimeInterface $EstimatedDate): self
    {
        $this->EstimatedDate = $EstimatedDate;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

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
