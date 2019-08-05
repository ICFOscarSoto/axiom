<?php

namespace App\Modules\HR\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRShiftsRepository")
 */
class HRShifts
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
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRSchedules", inversedBy="shifts")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\HR\Entity\HRPeriods", mappedBy="shift")
     */
    private $periods;

    public function __construct()
    {
        $this->periods = new ArrayCollection();
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

    public function getSchedules(): ?HRSchedules
    {
        return $this->schedules;
    }

    public function setSchedules(?HRSchedules $schedules): self
    {
        $this->schedules = $schedules;

        return $this;
    }

    /**
     * @return Collection|HRPeriods[]
     */
    public function getPeriods(): Collection
    {
        return $this->periods;
    }

    public function addPeriod(HRPeriods $period): self
    {
        if (!$this->periods->contains($period)) {
            $this->periods[] = $period;
            $period->setShift($this);
        }

        return $this;
    }

    public function removePeriod(HRPeriods $period): self
    {
        if ($this->periods->contains($period)) {
            $this->periods->removeElement($period);
            // set the owning side to null (unless already changed)
            if ($period->getShift() === $this) {
                $period->setShift(null);
            }
        }

        return $this;
    }
}
