<?php

namespace App\Modules\Cloud\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleUsers;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Cloud\Repository\CloudFilesRepository")
 */
class CloudFiles
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hashname;

    /**
     * @ORM\Column(type="float")
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $public;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateupd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

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
    * @ORM\Column(type="integer")
    */
   private $idclass;

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

    public function getUser(): ?GlobaleUsers
    {
        return $this->user;
    }

    public function setUser(?GlobaleUsers $user): self
    {
        $this->user = $user;

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

    public function getHashname(): ?string
    {
        return $this->hashname;
    }

    public function setHashname(string $hashname): self
    {
        $this->hashname = $hashname;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(float $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): self
    {
        $this->public = $public;

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

    public function getDateadd(): ?\DateTimeInterface
    {
        return $this->dateadd;
    }

    public function setDateadd(\DateTimeInterface $dateadd): self
    {
        $this->dateadd = $dateadd;

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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getIdclass(): ?int
    {
        return $this->idclass;
    }

    public function setIdclass(int $idclass): self
    {
        $this->idclass = $idclass;

        return $this;
    }
}
