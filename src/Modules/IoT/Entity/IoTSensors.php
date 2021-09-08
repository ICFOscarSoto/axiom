<?php

namespace App\Modules\IoT\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\IoT\Entity\IoTDevices;

/**
 * @ORM\Entity(repositoryClass="App\Modules\IoT\Repository\IoTSensorsRepository")
 */
class IoTSensors
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\IoT\Entity\IoTDevices", inversedBy="IoTSensors")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $device;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $unit;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $unitAbrv;

    /**
     * @ORM\Column(type="integer")
     */
    private $accuracy;

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
    private $max;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $min;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $notified=false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevice(): ?IoTDevices
    {
        return $this->device;
    }

    public function setDevice(?IoTDevices $device): self
    {
        $this->device = $device;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getUnitAbrv(): ?string
    {
        return $this->unitAbrv;
    }

    public function setUnitAbrv(?string $unitAbrv): self
    {
        $this->unitAbrv = $unitAbrv;

        return $this;
    }

    public function getAccuracy(): ?int
    {
        return $this->accuracy;
    }

    public function setAccuracy(int $accuracy): self
    {
        $this->accuracy = $accuracy;

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

    public function getMax(): ?float
    {
        return $this->max;
    }

    public function setMax(?float $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function setMin(?float $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getNotified(): ?bool
    {
        return $this->notified;
    }

    public function setNotified(?bool $notified): self
    {
        $this->notified = $notified;

        return $this;
    }
}
