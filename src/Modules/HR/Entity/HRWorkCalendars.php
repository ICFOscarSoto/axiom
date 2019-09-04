<?php

namespace App\Modules\HR\Entity;
/*
IMPORTANT: THIS CLASS ORIGINALY WAS THE HEAD OF THE RELATION.
           THE FIELDS NAME, CITY WAS MOVED TO HEAD HRWorkCalendarGroups BUT IT WAS LEAVED HERE FOR COMPATIBILITY
           THE FIELD WorkCalendarGroup was added.

*/
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\HR\Entity\HRWorkCalendarGroups;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRWorkCalendarsRepository")
 */
class HRWorkCalendars
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

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
     * @ORM\OneToMany(targetEntity="App\Modules\HR\Entity\HRHollidays", mappedBy="calendar")
     */
    private $holidays;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRWorkCalendarGroups")
     */
    private $workcalendargroup;

    public function __construct()
    {
        $this->holidays = new ArrayCollection();
    }

    public function isLeapYear(){
      $leap = date('L', mktime(0, 0, 0, 1, 1, $this->year));
      return !$leap;
    }

    public function getYearDays(){
      return ($this->isLeapYear())?365:364;
    }

    public function getWorkDays(){
      return $this->getYearDays()-count($this->holidays);
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

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

    /**
     * @return Collection|HRHollidays[]
     */
    public function getHolidays(): Collection
    {
        return $this->holidays;
    }

    public function addHoliday(HRHollidays $holiday): self
    {
        if (!$this->holidays->contains($holiday)) {
            $this->holidays[] = $holiday;
            $holiday->setCalendar($this);
        }

        return $this;
    }

    public function removeHoliday(HRHollidays $holiday): self
    {
        if ($this->holidays->contains($holiday)) {
            $this->holidays->removeElement($holiday);
            // set the owning side to null (unless already changed)
            if ($holiday->getCalendar() === $this) {
                $holiday->setCalendar(null);
            }
        }

        return $this;
    }

    public function getWorkcalendargroup(): ?HRWorkCalendarGroups
    {
        return $this->workcalendargroup;
    }

    public function setWorkcalendargroup(?HRWorkCalendarGroups $workcalendargroup): self
    {
        $this->workcalendargroup = $workcalendargroup;

        return $this;
    }
}
