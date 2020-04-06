<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPTransfersLineRepository")
 */
class ERPTransfersLine
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPTransfersHeader")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transfer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $item;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTransfer(): ?ERPTransfersHeader
    {
        return $this->transfer;
    }

    public function setTransfer(?ERPTransfersHeader $transfer): self
    {
        $this->transfer = $transfer;

        return $this;
    }

    public function getItem(): ?ERPProducts
    {
        return $this->item;
    }

    public function setItem(?ERPProducts $item): self
    {
        $this->item = $item;

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
}
