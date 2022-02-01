<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPCategories;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPCategoriesRepository")
 */
class ERPCategories
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCategories")
     * @ORM\JoinColumn(onDelete="Cascade")
     */
    private $parentid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateupd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active=true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $prestashopcategory;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $pathName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $pathId;



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

    public function getParentid(): ?ERPCategories
    {
        return $this->parentid;
    }

    public function setParentid(?ERPCategories $parentid): self
    {
        $this->parentid = $parentid;

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

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getPrestashopcategory(): ?int
    {
        return $this->prestashopcategory;
    }

    public function setPrestashopcategory(?int $prestashopcategory): self
    {
        $this->prestashopcategory = $prestashopcategory;

        return $this;
    }

    public function getPathName(): ?string
    {
        return $this->pathName;
    }

    public function setPathName(?string $pathName): self
    {
        $this->pathName = $pathName;

        return $this;
    }

    public function getPathId(): ?string
    {
        return $this->pathId;
    }

    public function setPathId(?string $pathId): self
    {
        $this->pathId = $pathId;

        return $this;
    }

  public function preProccess($kernel, $doctrine, $user, $params, $oldobj){
    $em = $doctrine->getManager();
    $categoriesRepository=$doctrine->getRepository(ERPCategories::class);
    $pipe='|';
    if ($oldobj==null) {
      if ($this->parentid==null) {
        $this->pathName=$this->name;
        $this->pathId=$pipe.$this->id.$pipe;
      } else {
        $this->pathName=$this->getParentid->getName().' -> '.$this->name;
        $this->pathId=$this->getParentid->getPathId().$this->id.$pipe;
      }
    } else {
      if ($this->name!=$oldobj->getName()){
        $categoriesRepository->updatePathName($oldobj->getName(),$this->name);
      }
    }
  }


}
