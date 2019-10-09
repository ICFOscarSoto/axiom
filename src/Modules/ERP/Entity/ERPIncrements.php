<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPCategories;
use \App\Modules\ERP\Entity\ERPCustomerGroups;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPIncrementsRepository")
 */
class ERPIncrements
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCategories")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomerGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customergroup;

    /**
     * @ORM\Column(type="float")
     */
    private $increment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active=1;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateupd;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupplier(): ?ERPSuppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?ERPSuppliers $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getCategory(): ?ERPCategories
    {
        return $this->category;
    }

    public function setCategory(?ERPCategories $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCustomergroup(): ?ERPCustomerGroups
    {
        return $this->customergroup;
    }

    public function setCustomergroup(?ERPCustomerGroups $customergroup): self
    {
        $this->customergroup = $customergroup;

        return $this;
    }

    public function getIncrement(): ?float
    {
        return $this->increment;
    }

    public function setIncrement(float $increment): self
    {
        $this->increment = $increment;

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

    public function getDateupd(): ?\DateTimeInterface
    {
        return $this->dateupd;
    }

    public function setDateupd(\DateTimeInterface $dateupd): self
    {
        $this->dateupd = $dateupd;

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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
    
    

public function formValidation($kernel, $doctrine, $user, $validationParams){
      $repository=$doctrine->getRepository(ERPIncrements::class);
      
      $valido=$repository->checkSupplierOnCategory($this->supplier, $this->category,$this->company);
      $repetido=$repository->checkRepeated($this->id,$this->supplier, $this->category,$this->customergroup,$this->company);
/*      
      if($valido==NULL)
        return ["valid"=>false, "global_errors"=>["No existe ningún producto para ese proveedor en esa categoría."]];
      else*/
      if($repetido!=NULL)
        return ["valid"=>false, "global_errors"=>["Ya existe un registro repetido para esos parámetros."]];
      else return ["valid"=>true];
      /*
      if($this->reduction_type==1)
        {
          if($this->reduction==NULL)
            return ["valid"=>false, "global_errors"=>["Por favor, introduce un descuento"]];
          else if($this->reduction<=0 OR $this->reduction>100)
            return ["valid"=>false, "global_errors"=>["Por favor, introduce un descuento correcto."]];
          else return ["valid"=>true];
        }
      else if($this->reduction_type==2 AND $this->amount==NULL)
          return ["valid"=>false, "global_errors"=>["Por favor, introduce precio neto"]];
      else if($valido!=NULL)
        return ["valid"=>false, "global_errors"=>["Ya existe un precio vigente para este producto y esta cantidad"]];
      else if($select==0)
        return ["valid"=>false, "global_errors"=>["Por favor, selecciona un tipo"]];
      else if($this->end<$this->start)
        return ["valid"=>false, "global_errors"=>["La fecha final es anterior a la fecha de inicio."]];
      else return ["valid"=>true];
    */
    }
    
    public function postProccess($kernel, $doctrine, $user){
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repository=$doctrine->getRepository(ERPSuppliers::class);
      $products=$repository->productsBySupplier($this->supplier->getId());
      foreach($products as $product){
        $productEntity=$repositoryProduct->findOneBy(["id"=>$product]);
        $productEntity->PVPCalculated($doctrine);
        $em->persist($productEntity);
        $em->flush();
      }
    }
}
