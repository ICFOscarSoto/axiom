<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleUserSessionsRepository")
 */
class GlobaleUserSessions
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=191)
     */
    private $sessionid;

    /**
     * @ORM\Column(type="text")
     */
    private $data;

    /**
     * @ORM\Column(type="integer")
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $ipaddress;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastactivity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lifetime;

    /**
     * @ORM\Column(type="boolean")
     */
    private $kick;


    public function getSessionid(): ?string
    {
        return $this->sessionid;
    }

    public function setSessionid(string $sessionid): self
    {
        $this->sessionid = $sessionid;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getUser(): ?GlobaleUsers
    {
        return $this->user;
    }

    public function setUser(?GlobaleUsers $user): self
    {
        $this->user = $user;

        return $this;
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

    public function getIpaddress(): ?string
    {
        return $this->ipaddress;
    }

    public function setIpaddress(?string $ipaddress): self
    {
        $this->ipaddress = $ipaddress;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getLastactivity(): ?\DateTimeInterface
    {
        return $this->lastactivity;
    }

    public function setLastactivity(?\DateTimeInterface $lastactivity): self
    {
        $this->lastactivity = $lastactivity;

        return $this;
    }

    public function getLifetime(): ?int
    {
        return $this->lifetime;
    }

    public function setLifetime(int $lifetime): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    public function getKick(): ?bool
    {
        return $this->kick;
    }

    public function setKick(bool $kick): self
    {
        $this->kick = $kick;

        return $this;
    }
}
