<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobalePermissionsRoutes;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobalePermissionsRoutesUsersRepository")
 */
class GlobalePermissionsRoutesUsers
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobalePermissionsRoutes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $permissionroute;

    /**
     * @ORM\Column(type="smallint")
     */
    private $allowaccess=3; //

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

    public function getPermissionroute(): ?GlobalePermissionsRoutes
    {
        return $this->permissionroute;
    }

    public function setPermissionroute(?GlobalePermissionsRoutes $permissionroute): self
    {
        $this->permissionroute = $permissionroute;

        return $this;
    }

    public function getAllowaccess(): ?int
    {
        return $this->allowaccess;
    }

    public function setAllowaccess(int $allowaccess): self
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
