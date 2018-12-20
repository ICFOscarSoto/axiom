<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\MenuOptionsRepository")
 */
class MenuOptions
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $rute;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $icon;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parent;

    public $childs;
    public $url;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\Companies", inversedBy="menuOptions")
     */
    private $company;

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

    public function getRute(): ?string
    {
        return $this->rute;
    }

    public function setRute(?string $rute): self
    {
        $this->rute = $rute;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function setParent(?int $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getCompany(): ?Companies
    {
        return $this->company;
    }

    public function setCompany(?Companies $company): self
    {
        $this->company = $company;

        return $this;
    }
}
