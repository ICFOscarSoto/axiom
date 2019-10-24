<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPCategories;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPShoppingDiscountsRepository")
 */
class ERPShoppingDiscounts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCategories")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $category;

    /**
     * @ORM\Column(type="float")
     */
    private $discount;

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

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(string $discount): self
    {
        $this->discount = $discount;

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
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repository=$doctrine->getRepository(ERPSuppliers::class);
      $products=$repository->productsBySupplier($this->supplier->getId());
      foreach($products as $product){
        $productEntity=$repositoryProduct->findOneBy(["id"=>$product]);
        $productEntity->priceCalculated($doctrine);
        $em->persist($productEntity);
        $em->flush();
      }

    }

    public function delete($doctrine){
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repository=$doctrine->getRepository(ERPSuppliers::class);
      $products=$repository->productsBySupplier($this->supplier->getId());
      foreach($products as $product){
        $productEntity=$repositoryProduct->findOneBy(["id"=>$product]);
        $productEntity->priceCalculated($doctrine);
        dump($productEntity->getShoppingDiscount($doctrine));
        $em->persist($productEntity);
        $em->flush();
      }
    }

}
