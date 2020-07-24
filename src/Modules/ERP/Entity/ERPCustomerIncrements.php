<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPCategories;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\ERP\Entity\ERPCustomerPrices;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPCustomerIncrementsRepository")
 */
class ERPCustomerIncrements
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
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="float")
     */
    private $increment;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

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

    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

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

    public function postProccess($kernel, $doctrine, $user){
      $this->calculateIncrements($doctrine);
    }

    public function delete($doctrine){
        $this->calculateIncrements($doctrine);
      }


    public function calculateIncrements($doctrine){
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repositoryCustomerPrices=$doctrine->getRepository(ERPCustomerPrices::class);
      $repositoryCustomerGroups=$doctrine->getRepository(ERPCustomerGroups::class);
      $repositorySuppliers=$doctrine->getRepository(ERPSuppliers::class);
      $repositoryIncrements=$doctrine->getRepository(ERPIncrements::class);
      //de momento partimos de que el proveedor nunca es NULL, pero en el futuro tendremos
      //que permitir que pueda haber incrementos sólo por categría, por lo que esto no funcionaría.
      $products=$repositorySuppliers->productsBySupplier($this->supplier->getId());
      foreach($products as $product){
        $productEntity=$repositoryProduct->findOneBy(["id"=>$product]);
        //controlamos que un producto no pueda recibir incrementos si no tiene una categoría asocaida
        if($productEntity->getCategory()!=null)
        {

            $increment=$this->getIncrementByCustomer($doctrine,$this->supplier,$productEntity->getCategory(),$this->customer);
            if($increment!=NULL){
              if($repositoryCustomerPrices->existPrice($productEntity,$this->customer,$this->supplier)){
                    $customerpricesEntity=$repositoryCustomerPrices->findOneBy(["product"=>$productEntity,"customer"=>$this->customer,"supplier"=>$this->supplier]);
                    $customerpricesEntity->setIncrement($increment);
                    $customerpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
                    $customerpricesEntity->setStart($this->getStart());
                    $customerpricesEntity->setEnd($this->getEnd());
                }
              else {
                    $customerpricesEntity= new ERPCustomerPrices();
                    $customerpricesEntity->setProduct($productEntity);
                    $customerpricesEntity->setCustomer($this->customer);
                    $customerpricesEntity->setSupplier($this->supplier);
                    $customerpricesEntity->setIncrement($increment*1);
                    $customerpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
                    $customerpricesEntity->setActive(1);
                    $customerpricesEntity->setDeleted(0);
                    $customerpricesEntity->setDateupd(new \DateTime());
                    $customerpricesEntity->setDateadd(new \DateTime());
                    $customerpricesEntity->setStart($this->getStart());
                    $customerpricesEntity->setEnd($this->getEnd());

              }
                  $em->persist($customerpricesEntity);
            }

            //$em->persist($productEntity);
            $em->flush();

          }
      }
      $em->clear();

    }


    public function calculateIncrementsBySupplierCategory($doctrine){
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repositoryCustomerPrices=$doctrine->getRepository(ERPCustomerPrices::class);
      $repositoryCustomerGroups=$doctrine->getRepository(ERPCustomerGroups::class);
      $repositorySuppliers=$doctrine->getRepository(ERPSuppliers::class);
      $repositoryIncrements=$doctrine->getRepository(ERPIncrements::class);
      //de momento partimos de que el proveedor nunca es NULL, pero en el futuro tendremos
      //que permitir que pueda haber incrementos sólo por categría, por lo que esto no funcionaría.
      $products=$repositoryProduct->productsBySupplierCategory($this->supplier->getId(),$this->category->getId());
      foreach($products as $product){

        $productEntity=$repositoryProduct->findOneBy(["id"=>$product]);
        //controlamos que un producto no pueda recibir incrementos si no tiene una categoría asocaida
      //  dump("Calculamos el incremento para el producto ".$productEntity->getCode());
        if($productEntity->getCategory()!=null)
        {

            $increment=$this->getIncrementByCustomer($doctrine,$this->supplier,$productEntity->getCategory(),$this->customer);
            if($increment!=NULL){
              if($repositoryCustomerPrices->existPrice($productEntity,$this->customer,$this->supplier)){
                    $customerpricesEntity=$repositoryCustomerPrices->findOneBy(["product"=>$productEntity,"customer"=>$this->customer,"supplier"=>$this->supplier]);
                    $customerpricesEntity->setIncrement($increment);
                    $customerpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
                    $customerpricesEntity->setStart($this->getStart());
                    $customerpricesEntity->setEnd($this->getEnd());
                }
              else {
                    $customerpricesEntity= new ERPCustomerPrices();
                    $customerpricesEntity->setProduct($productEntity);
                    $customerpricesEntity->setCustomer($this->customer);
                    $customerpricesEntity->setSupplier($this->supplier);
                    $customerpricesEntity->setIncrement($increment*1);
                    $customerpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
                    $customerpricesEntity->setActive(1);
                    $customerpricesEntity->setDeleted(0);
                    $customerpricesEntity->setDateupd(new \DateTime());
                    $customerpricesEntity->setDateadd(new \DateTime());
                    $customerpricesEntity->setStart($this->getStart());
                    $customerpricesEntity->setEnd($this->getEnd());

              }
                  $em->persist($customerpricesEntity);
            }

            //$em->persist($productEntity);
            $em->flush();

          }
      }
      //$em->clear();

    }

/*
    public function calculateIncrementsByProduct($doctrine,$product_id){
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repositoryCustomerPrices=$doctrine->getRepository(ERPCustomerPrices::class);
      $repositoryCustomerGroups=$doctrine->getRepository(ERPCustomerGroups::class);
      $repositorySuppliers=$doctrine->getRepository(ERPSuppliers::class);
      $repositoryIncrements=$doctrine->getRepository(ERPIncrements::class);
      //de momento partimos de que el proveedor nunca es NULL, pero en el futuro tendremos
      //que permitir que pueda haber incrementos sólo por categría, por lo que esto no funcionaría.
    //  $products=$repositorySuppliers->productsBySupplier($this->supplier->getId());
        $productEntity=$repositoryProduct->findOneBy(["id"=>$product_id]);
        //controlamos que un producto no pueda recibir incrementos si no tiene una categoría asocaida
        if($productEntity->getCategory()!=null)
        {

            $increment=$this->getIncrementByCustomer($doctrine,$this->supplier,$productEntity->getCategory(),$this->customer);
            if($increment!=NULL){
              if($repositoryCustomerPrices->existPrice($productEntity,$this->customer,$this->supplier)){
                    $customerpricesEntity=$repositoryCustomerPrices->findOneBy(["product"=>$productEntity,"customer"=>$this->customer,"supplier"=>$this->supplier]);
                    $customerpricesEntity->setIncrement($increment);
                    $customerpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
                    $customerpricesEntity->setStart($this->getStart());
                    $customerpricesEntity->setEnd($this->getEnd());
                }
              else {
                    $customerpricesEntity= new ERPCustomerPrices();
                    $customerpricesEntity->setProduct($productEntity);
                    $customerpricesEntity->setCustomer($this->customer);
                    $customerpricesEntity->setSupplier($this->supplier);
                    $customerpricesEntity->setIncrement($increment*1);
                    $customerpricesEntity->setPrice(round($productEntity->getShoppingPrice()*(1+($increment/100)),2));
                    $customerpricesEntity->setActive(1);
                    $customerpricesEntity->setDeleted(0);
                    $customerpricesEntity->setDateupd(new \DateTime());
                    $customerpricesEntity->setDateadd(new \DateTime());
                    $customerpricesEntity->setStart($this->getStart());
                    $customerpricesEntity->setEnd($this->getEnd());

              }
                  $em->persist($customerpricesEntity);
            }
            $em->flush();
          }

          $em->clear();
    }
*/
    public function getIncrementByCustomer($doctrine,$supplier,$productcategory,$customer)
    {
      $repository=$doctrine->getRepository(ERPCustomerIncrements::class);
      $category=$productcategory;
      $incrementbycustomer=$repository->getIncrementByCustomer($supplier,$category,$customer);

      while ($category->getParentid()!=null && $incrementbycustomer==null){
            $category=$category->getParentid();
            $incrementbycustomer=$repository->getIncrementByCustomer($supplier,$category,$customer);
        }

        if($incrementbycustomer==null){
            $incrementbycustomer=$repository->getIncrementByCustomer($supplier,null,$customer);
        }

        if ($incrementbycustomer==null){
        $category=$productcategory;
        $incrementbycustomer=$repository->getIncrementByCustomer(null,$category,$customer);
        while ($category->getParentid()!=null && $incrementbycustomer==null){
            $category=$category->getParentid();
            $incrementbycustomer=$repository->getIncrementByCustomer(null,$category,$customer);
          }
        }

      return $incrementbycustomer;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }
}
