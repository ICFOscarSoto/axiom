<?php

namespace App\Entity\Globale;

use Doctrine\ORM\Mapping as ORM;

/**
 * GlobalMenuoptions
 *
 * @ORM\Table(name="global_menuoptions", indexes={@ORM\Index(name="FK_MENUOPTIONS_COMPANIES", columns={"id_company"})})
 * @ORM\Entity(repositoryClass="App\Repository\Globale\MenuOptionsRepository")
 */
class MenuOptions
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rute", type="string", length=150, nullable=true, options={"default"="NULL"})
     */
    private $rute = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="roles", type="text", length=0, nullable=true, options={"default"="NULL"})
     */
    private $roles = 'NULL';

    /**
     * @var string|null
     *
     * @ORM\Column(name="icon", type="string", length=150, nullable=true, options={"default"="NULL"})
     */
    private $icon = 'NULL';

    /**
     * @var int|null
     *
     * @ORM\Column(name="parent", type="integer", nullable=true, options={"default"="NULL"})
     */
    private $parent = 'NULL';

    /**
     * @var \GlobalCompanies
     *
     * @ORM\ManyToOne(targetEntity="Companies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_company", referencedColumnName="id")
     * })
     */
    private $idCompany;
	public $childs;
	public $url;

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

    public function getRoles(): ?string
    {
        return $this->roles;
    }

    public function setRoles(?string $roles): self
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

    public function getIdCompany(): ?Companies
    {
        return $this->idCompany;
    }

    public function setIdCompany(?Companies $idCompany): self
    {
        $this->idCompany = $idCompany;

        return $this;
    }


}
