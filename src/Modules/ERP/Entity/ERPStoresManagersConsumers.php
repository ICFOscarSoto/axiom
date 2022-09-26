<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\ERP\Entity\ERPStoresManagersConsumersRepository")
 */
class ERPStoresManagersConsumers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $idcard;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $code2;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $nfcid;

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
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoresManagers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getIdcard(): ?string
    {
        return $this->idcard;
    }

    public function setIdcard(string $idcard): self
    {
        $this->idcard = $idcard;

        return $this;
    }

    public function getCode2(): ?string
    {
        return $this->code2;
    }

    public function setCode2(?string $code2): self
    {
        $this->code2 = $code2;

        return $this;
    }

    public function getNfcid(): ?string
    {
        return $this->nfcid;
    }

    public function setNfcid(?string $nfcid): self
    {
        $this->nfcid = $nfcid;

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

    public function getManager(): ?ERPStoresManagers
    {
        return $this->manager;
    }

    public function setManager(?ERPStoresManagers $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $validationParams){
      $repository=$doctrine->getRepository(ERPStoresManagersConsumers::class);
      $fieldErrors=[];
      $obj=$repository->findOneBy(["nfcid"=>$this->nfcid,"deleted"=>0, "manager"=>$this->manager]);
      if($obj!=null && $obj->getId()!=$this->getId() && $this->nfcid!=null){
        return ["valid"=>false, "global_errors"=>["La tarjeta NFC ya esta asignada a otro trabajador."]];
      }else {
        return ["valid"=>empty($fieldErrors), "field_errors"=>$fieldErrors];
      }
    }
}
