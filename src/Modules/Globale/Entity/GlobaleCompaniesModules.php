<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleModules;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleCompaniesModulesRepository")
 */
class GlobaleCompaniesModules
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $companyown;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleModules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $module;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active=1;

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

    public function getCompanyown(): ?GlobaleCompanies
    {
        return $this->companyown;
    }

    public function setCompanyown(?GlobaleCompanies $companyown): self
    {
        $this->companyown = $companyown;

        return $this;
    }

    public function getModule(): ?GlobaleModules
    {
        return $this->module;
    }

    public function setModule(?GlobaleModules $module): self
    {
        $this->module = $module;

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

    public function postProccess($kernel, $doctrine, $user){
      //Create general configuration of the module
      $class="\App\Modules\\".$this->module->getName()."\Entity\\".$this->module->getName()."Configuration";
      if(class_exists($class)){
        $repository=$doctrine->getRepository($class);
        //Check if already exist a Configuration
        $config=$repository->findOneBy(["company"=>$this->companyown]);
        if(!$config){
          //No exist the configuration, create interface
          $config=new $class($kernel, $doctrine, $user, $this->companyown);
          $config->setCompany($this->companyown);
          $doctrine->getManager()->persist($config);
          $doctrine->getManager()->flush();
        }

      }

    }

}
