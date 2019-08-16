<?php

namespace App\Modules\Tracker\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Tracker\Entity\TrackerTrackers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Tracker\Repository\TrackerLocationsRepository")
 */
class TrackerLocations
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Tracker\Entity\TrackerTrackers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $tracker;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $hdop;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $sats;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $age;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $altitude;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $course;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $kmph;

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

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHdop(): ?int
    {
        return $this->hdop;
    }

    public function setHdop(?int $hdop): self
    {
        $this->hdop = $hdop;

        return $this;
    }

    public function getSats(): ?int
    {
        return $this->sats;
    }

    public function setSats(?int $sats): self
    {
        $this->sats = $sats;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    public function setAltitude(?int $altitude): self
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getCourse(): ?int
    {
        return $this->course;
    }

    public function setCourse(?int $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getKmph(): ?int
    {
        return $this->kmph;
    }

    public function setKmph(?int $kmph): self
    {
        $this->kmph = $kmph;

        return $this;
    }

    public function getTracker(): ?TrackerTrackers
    {
        return $this->tracker;
    }

    public function setTracker(?TrackerTrackers $tracker): self
    {
        $this->tracker = $tracker;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
