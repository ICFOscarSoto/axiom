<?php

namespace App\Modules\IoT\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\IoT\Repository\IoTDeviceRepository")
 */
class IoTDevices
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\IoT\Entity\IoTSensors", mappedBy="device")
     */
    private $IoTSensors;

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

    public function __construct()
    {
        $this->IoTSensors = new ArrayCollection();
    }

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

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection|IoTSensors[]
     */
    public function getIoTSensors(): Collection
    {
        return $this->IoTSensors;
    }

    public function addIoTSensor(IoTSensors $ioTSensor): self
    {
        if (!$this->IoTSensors->contains($ioTSensor)) {
            $this->IoTSensors[] = $ioTSensor;
            $ioTSensor->setDevice($this);
        }

        return $this;
    }

    public function removeIoTSensor(IoTSensors $ioTSensor): self
    {
        if ($this->IoTSensors->contains($ioTSensor)) {
            $this->IoTSensors->removeElement($ioTSensor);
            // set the owning side to null (unless already changed)
            if ($ioTSensor->getDevice() === $this) {
                $ioTSensor->setDevice(null);
            }
        }

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
