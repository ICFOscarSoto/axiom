<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\ERP\Entity\ERPProductsVariants;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Utils\ERPPrestashopUtils;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPEAN13Repository")
 */
class ERPEAN13
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers", fetch="EAGER")
     * @ORM\JoinColumn(nullable=true,onDelete="CASCADE")
     */
    private $customer;

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
    private $active=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted=0;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProductsVariants")
     */
    private $productvariant;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $author;

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

    public function getSupplier(): ?ERPSuppliers
    {
        return $this->supplier;
    }

    public function setSupplier(?ERPSuppliers $supplier): self
    {
        $this->supplier = $supplier;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getProductVariant(): ?ERPProductsVariants
    {
        return $this->productvariant;
    }

    public function setProductVariant(?ERPProductsVariants $productvariant): self
    {
        $this->productvariant = $productvariant;

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

    public function preProccess($kernel, $doctrine, $user, $params, $oldobj){
      if ($this->getProductvariant()!=null && $this->getProductvariant()->getVariant()!=null && $this->getProductvariant()->getVariant()->getId()==null)
        $this->getProductvariant()->setVariant(null);
      if ($this->getType()==1)
        $this->setCustomer(null);
      else
        $this->setSupplier(null);
    }
/*
    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){

      $productRepository=$doctrine->getRepository(ERPProducts::class);
      $product=$productRepository->findOneBy(["id"=>$this->getProduct()->getId()]);

      if($productRepository->getVariants($product->getId()))
      {

      }
      else{

      $repository=$doctrine->getRepository(ERPEAN13::class);
      $ean13=$repository->findOneBy(["product"=>$product,"supplier"=>$product->getSupplier(),"active"=>1,"deleted"=>0]);
      if($this->name==$ean13->getName()) {

        $array_new_data = [];
        $array_new_data["ean13"]=$this->name;

        $webproductRepository=$doctrine->getRepository(ERPWebProducts::class);
        $webproduct=$webproductRepository->findOneBy(["product"=>$product]);

        $prestashopUtils= new ERPPrestashopUtils();
        $prestashopUtils->updateWebProduct($doctrine,$array_new_data,$product,$webproduct);


      }


      }

  }
*/

}
