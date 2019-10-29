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
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCategories")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomerGroups")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
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
      $repetido=$repository->checkRepeated($this->id,$this->supplier, $this->category,$this->customergroup,$this->company);
      if($repetido!=NULL)
        return ["valid"=>false, "global_errors"=>["Ya existe un incremento establecido para esos parámetros."]];
      else return ["valid"=>true];
    }

    public function postProccess($kernel, $doctrine, $user){
      $this->calculateIncrements($doctrine);
    }
    
    public function delete($doctrine){
      $this->calculateIncrements($doctrine);
    }
    
    public function calculateIncrements($doctrine){
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repositoryProductPrices=$doctrine->getRepository(ERPProductPrices::class);
      $repositoryCustomerGroups=$doctrine->getRepository(ERPCustomerGroups::class);
      $repositorySuppliers=$doctrine->getRepository(ERPSuppliers::class);
      $repositoryIncrements=$doctrine->getRepository(ERPIncrements::class);
      //de momento partimos de que el proveedor nunca es NULL, pero en el futuro tendremos 
      //que permitir que pueda haber incrementos sólo por catogría, por lo que esto no funcionaría.
      $products=$repositorySuppliers->productsBySupplier($this->supplier->getId());
      foreach($products as $product){
        $productEntity=$repositoryProduct->findOneBy(["id"=>$product]);
        $productEntity->calculatePVP($doctrine);
        $increment=$this->getIncrementByGroup($doctrine,$this->supplier,$productEntity->getCategory(),$this->customergroup);
        if($increment!=NULL){
          if($repositoryProductPrices->existPrice($productEntity,$this->customergroup)){
                $productpricesEntity=$repositoryProductPrices->findOneBy(["product"=>$productEntity,"customergroup"=>$this->customergroup]);
                $productpricesEntity->setIncrement($increment);
                $productpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
            }
          else {
                $productpricesEntity= new ERPProductPrices();
                $productpricesEntity->setProduct($productEntity);
                $productpricesEntity->setCustomergroup($this->customergroup);
                $productpricesEntity->setIncrement($increment*1);
                $productpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
                $productpricesEntity->setActive(1);
                $productpricesEntity->setDeleted(0);
                $productpricesEntity->setDateupd(new \DateTime());
                $productpricesEntity->setDateadd(new \DateTime());

          }
              $em->persist($productpricesEntity);
        }

        $em->persist($productEntity);
        $em->flush();
      }
    
    }


    public function getIncrementByGroup($doctrine,$supplier,$productcategory,$customergroup)
    {
      $repository=$doctrine->getRepository(ERPIncrements::class);
      $category=$productcategory;
      $incrementbygroup=$repository->getIncrementByGroup($supplier,$category,$customergroup);
      
      while ($category->getParentid()!=null && $incrementbygroup==null){
            $category=$category->getParentid();
            $incrementbygroup=$repository->getIncrementByGroup($supplier,$category,$customergroup);
        }

        if($incrementbygroup==null){
            $incrementbygroup=$repository->getIncrementByGroup($supplier,null,$customergroup);
        }

        if ($incrementbygroup==null){
        $category=$productcategory;
        $incrementbygroup=$repository->getIncrementByGroup(null,$category,$customergroup);
        while ($category->getParentid()!=null && $incrementbygroup==null){
            $category=$category->getParentid();
            $incrementbygroup=$repository->getIncrementByGroup(null,$category,$customergroup);
          }
        }

      if($incrementbygroup==null){
        $repository=$doctrine->getRepository(ERPCustomerGroups::class);
        $incrementbygroup=$repository->getIncrement($customergroup);
        return $incrementbygroup;

      }
      return $incrementbygroup;
    }
    
    
}
