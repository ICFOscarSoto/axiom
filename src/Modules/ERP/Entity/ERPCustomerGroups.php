<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPCustomerGroupsRepository")
 */
class ERPCustomerGroups
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="integer")
     */
    private $rate;


    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $validationParams){
      //Check for overlapped periods
      $repository=$doctrine->getRepository(ERPCustomerGroups::class);
      $exists=$repository->checkRepeated($this->id,$this->name);
      if($exists!=null)
        return ["valid"=>false, "global_errors"=>["Ya existe un grupo de clientes con ese nombre"]];
      else return ["valid"=>true];
    }

    public function preProccess($kernel, $doctrine, $user){
        $this->name=strtoupper($this->name);

      }


      public function getIncrementByGroup($doctrine,$supplier,$productcategory){

        $repository=$doctrine->getRepository(ERPIncrements::class);
        $category=$productcategory;

        $incrementbygroup=$repository->getIncrementByGroup($supplier,$category,$this);

        while ($category->getParentid()!=null && $incrementbygroup==null){
              $category=$category->getParentid();
              $incrementbygroup=$repository->getIncrementByGroup($supplier,$category,$this);
          }

      if($incrementbygroup==null){
        $repository=$doctrine->getRepository(ERPCustomerGroups::class);
        $incrementbygroup=$repository->getIncrement($this);
        return $incrementbygroup;

      }
      return $incrementbygroup;
      }
}
