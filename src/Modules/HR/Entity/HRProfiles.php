<?php

namespace App\Modules\HR\Entity;

use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRProfilesRepository")
 */
class HRProfiles
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRDepartments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $primaryeducation;

    /**
     * @ORM\Column(type="boolean")
     */
    private $secondaryeducation;

    /**
     * @ORM\Column(type="boolean")
     */
    private $middlegraduate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $superiorgraduate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fp1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fp2;

    /**
     * @ORM\Column(type="boolean")
     */
    private $speciallicense;

    /**
     * @ORM\Column(type="boolean")
     */
    private $driverlicense;

    /**
     * @ORM\Column(type="boolean")
     */
    private $other;

    /**
     * @ORM\Column(type="boolean")
     */
    private $notnecessary;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $responsability;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $knowledges;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $experience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers", inversedBy="hRProfiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

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

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRProfiles")
     */
    private $parent;

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

    public function getDepartment(): ?HRDepartments
    {
        return $this->department;
    }

    public function setDepartment(?HRDepartments $department): self
    {
        $this->department = $department;

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

    public function getPrimaryeducation(): ?bool
    {
        return $this->primaryeducation;
    }

    public function setPrimaryeducation(bool $primaryeducation): self
    {
        $this->primaryeducation = $primaryeducation;

        return $this;
    }

    public function getSecondaryeducation(): ?bool
    {
        return $this->secondaryeducation;
    }

    public function setSecondaryeducation(bool $secondaryeducation): self
    {
        $this->secondaryeducation = $secondaryeducation;

        return $this;
    }

    public function getMiddlegraduate(): ?bool
    {
        return $this->middlegraduate;
    }

    public function setMiddlegraduate(bool $middlegraduate): self
    {
        $this->middlegraduate = $middlegraduate;

        return $this;
    }

    public function getSuperiorgraduate(): ?bool
    {
        return $this->superiorgraduate;
    }

    public function setSuperiorgraduate(bool $superiorgraduate): self
    {
        $this->superiorgraduate = $superiorgraduate;

        return $this;
    }

    public function getFp1(): ?bool
    {
        return $this->fp1;
    }

    public function setFp1(bool $fp1): self
    {
        $this->fp1 = $fp1;

        return $this;
    }

    public function getFp2(): ?bool
    {
        return $this->fp2;
    }

    public function setFp2(bool $fp2): self
    {
        $this->fp2 = $fp2;

        return $this;
    }

    public function getSpeciallicense(): ?bool
    {
        return $this->speciallicense;
    }

    public function setSpeciallicense(bool $speciallicense): self
    {
        $this->speciallicense = $speciallicense;

        return $this;
    }

    public function getDriverlicense(): ?bool
    {
        return $this->driverlicense;
    }

    public function setDriverlicense(bool $driverlicense): self
    {
        $this->driverlicense = $driverlicense;

        return $this;
    }

    public function getOther(): ?bool
    {
        return $this->other;
    }

    public function setOther(bool $other): self
    {
        $this->other = $other;

        return $this;
    }

    public function getNotnecessary(): ?bool
    {
        return $this->notnecessary;
    }

    public function setNotnecessary(bool $notnecessary): self
    {
        $this->notnecessary = $notnecessary;

        return $this;
    }

    public function getResponsability(): ?string
    {
        return $this->responsability;
    }

    public function setResponsability(?string $responsability): self
    {
        $this->responsability = $responsability;

        return $this;
    }

    public function getKnowledges(): ?string
    {
        return $this->knowledges;
    }

    public function setKnowledges(?string $knowledges): self
    {
        $this->knowledges = $knowledges;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getAuthor(): ?GlobaleUsers
    {
        return $this->author;
    }

    public function setAuthor(?GlobaleUsers $author): self
    {
        $this->author = $author;

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

    public function getParent(): ?HRProfiles
    {
        return $this->parent;
    }

    public function setParent(?HRProfiles $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
