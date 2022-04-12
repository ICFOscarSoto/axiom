<?php

namespace App\Modules\Vehicles\Entity;

use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\HR\Entity\HRWorkers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Vehicles\Repository\VehiclesUsesRepository")
 */
class VehiclesUses
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Vehicles\Entity\VehiclesVehicles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRWorkers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $worker;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $startlatitude;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $startlongitude;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $endlatitude;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $endlongitude;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

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

    public function getVehicle(): ?VehiclesVehicles
    {
        return $this->vehicle;
    }

    public function setVehicle(?VehiclesVehicles $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getWorker(): ?HRWorkers
    {
        return $this->worker;
    }

    public function setWorker(?HRWorkers $worker): self
    {
        $this->worker = $worker;

        return $this;
    }

    public function getAuthor(): ?GlobaleUsers
    {
        return $this->author;
    }

    public function setAuthor(?GlobaleUsers $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getStartlatitude(): ?string
    {
        return $this->startlatitude;
    }

    public function setStartlatitude(?string $startlatitude): self
    {
        $this->startlatitude = $startlatitude;

        return $this;
    }

    public function getStartlongitude(): ?string
    {
        return $this->startlongitude;
    }

    public function setStartlongitude(?string $startlongitude): self
    {
        $this->startlongitude = $startlongitude;

        return $this;
    }

    public function getEndlatitude(): ?string
    {
        return $this->endlatitude;
    }

    public function setEndlatitude(?string $endlatitude): self
    {
        $this->endlatitude = $endlatitude;

        return $this;
    }

    public function getEndlongitude(): ?string
    {
        return $this->endlongitude;
    }

    public function setEndlongitude(?string $endlongitude): self
    {
        $this->endlongitude = $endlongitude;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

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
