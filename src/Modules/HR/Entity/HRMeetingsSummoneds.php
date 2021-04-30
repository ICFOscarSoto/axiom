<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\HR\Entity\HRMeetingsSummonedsRepository")
 */
class HRMeetingsSummoneds
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRMeetings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $meeting;

    /**
     * @ORM\Column(type="boolean")
     */
    private $witness=0;

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
    private $active=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted=0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sended=0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRWorkers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $worker;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMeeting(): ?HRMeetings
    {
        return $this->meeting;
    }

    public function setMeeting(?HRMeetings $meeting): self
    {
        $this->meeting = $meeting;

        return $this;
    }

    public function getWitness(): ?bool
    {
        return $this->witness;
    }

    public function setWitness(bool $witness): self
    {
        $this->witness = $witness;

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

    public function getSended(): ?bool
    {
        return $this->sended;
    }

    public function setSended(bool $sended): self
    {
        $this->sended = $sended;

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
}
