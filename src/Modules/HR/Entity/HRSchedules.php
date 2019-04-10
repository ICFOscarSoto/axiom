<?php

namespace App\Modules\HR\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\HR\Entity\HRShifts;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRSchedulesRepository")
 */
class HRSchedules
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $rotation;

    /**
     * @ORM\Column(type="smallint")
     */
    private $period;

    /**
     * @ORM\OneToMany(targetEntity="\App\Modules\HR\Entity\HRShifts", mappedBy="schedules")
     */
    private $shifts;

    public function __construct()
    {
        $this->shifts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRotation(): ?int
    {
        return $this->rotation;
    }

    public function setRotation(int $rotation): self
    {
        $this->rotation = $rotation;

        return $this;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(int $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return Collection|HRShifts[]
     */
    public function getShifts(): Collection
    {
        return $this->shifts;
    }

    public function addShift(HRShifts $shift): self
    {
        if (!$this->shifts->contains($shift)) {
            $this->shifts[] = $shift;
            $shift->setSchedules($this);
        }

        return $this;
    }

    public function removeShift(HRShifts $shift): self
    {
        if ($this->shifts->contains($shift)) {
            $this->shifts->removeElement($shift);
            // set the owning side to null (unless already changed)
            if ($shift->getSchedules() === $this) {
                $shift->setSchedules(null);
            }
        }

        return $this;
    }
}
