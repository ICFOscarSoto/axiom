<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobalePermissionsZones;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobalePermissionsZonesUsersRepository")
 */
class GlobalePermissionsZonesUsers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobalePermissionsZones")
     * @ORM\JoinColumn(nullable=false)
     */
    private $permissionzone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allowaccess;

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

    public function getUser(): ?GlobaleUsers
    {
        return $this->user;
    }

    public function setUser(?GlobaleUsers $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPermissionzone(): ?GlobalePermissionsZones
    {
        return $this->permissionzone;
    }

    public function setPermissionzone(?GlobalePermissionsZones $permissionzone): self
    {
        $this->permissionzone = $permissionzone;

        return $this;
    }

    public function getAllowaccess(): ?bool
    {
        return $this->allowaccess;
    }

    public function setAllowaccess(bool $allowaccess): self
    {
        $this->allowaccess = $allowaccess;

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
