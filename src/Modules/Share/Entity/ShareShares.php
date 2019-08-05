<?php

namespace App\Modules\Share\Entity;

use App\Entity\Globale\GlobaleUserGroups;
use App\Entity\Globale\GlobaleUsers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Calendar\Repository\ShareSharesRepository")
 */
class ShareShares
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=125)
     */
    private $className;

    /**
     * @ORM\Column(type="integer")
     */
    private $idObject;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUserGroups")
     */
    private $userGroup;

    /**
     * @ORM\Column(type="boolean")
     */
    private $readOnly;

    /**
     * @ORM\Column(type="boolean")
     */
    private $shareable;

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

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    /*public function getUser(): ?GlobaleUsers
    {
        return $this->user;
    }

    public function setUser(?GlobaleUsers $user): self
    {
        $this->user = $user;

        return $this;
    }*/
/*
    public function getUserGroup(): ?GlobaleUserGroups
    {
        return $this->userGroup;
    }

    public function setUserGroup(?GlobaleUserGroups $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }
*/
    public function getReadOnly(): ?bool
    {
        return $this->readOnly;
    }

    public function setReadOnly(bool $readOnly): self
    {
        $this->readOnly = $readOnly;

        return $this;
    }

    public function getShareable(): ?bool
    {
        return $this->shareable;
    }

    public function setShareable(bool $shareable): self
    {
        $this->shareable = $shareable;

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

    public function getIdObject(): ?int
    {
        return $this->idObject;
    }

    public function setIdObject(int $idObject): self
    {
        $this->idObject = $idObject;

        return $this;
    }
}
