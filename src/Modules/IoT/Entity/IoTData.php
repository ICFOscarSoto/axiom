<?php

namespace App\Modules\IoT\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\IoT\Entity\IoTSensors;

/**
 * @ORM\Entity(repositoryClass="App\Modules\IoT\Repository\IoTDataRepository")
 */
class IoTData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\IoT\Entity\IoTSensors")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $sensor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="integer")
     */
    private $counter;

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

    public function getSensor(): ?IoTSensors
    {
        return $this->sensor;
    }

    public function setSensor(?IoTSensors $sensor): self
    {
        $this->sensor = $sensor;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCounter(): ?int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): self
    {
        $this->counter = $counter;

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

    public function getBoolean(): ?string
    {
        return $this->boolean;
    }

    public function setBoolean(string $boolean): self
    {
        $this->boolean = $boolean;

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
