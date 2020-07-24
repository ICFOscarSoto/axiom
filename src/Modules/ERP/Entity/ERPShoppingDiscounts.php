<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPCategories;
use \App\Modules\ERP\Entity\ERPIncrements;

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

    /**
     * @ORM\Column(type="float")
     */
    private $discount1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount2;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount3;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $discount4;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

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
    $this->setShoppingPrices($doctrine);
    }

    public function delete($doctrine){
    $this->setShoppingPrices($doctrine);
    }

    public function setShoppingPrices($doctrine){
      $em = $doctrine->getManager();
      $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
      $repository=$doctrine->getRepository(ERPSuppliers::class);
      $repositoryIncrements=$doctrine->getRepository(ERPIncrements::class);
      $products=$repository->productsBySupplier($this->supplier->getId());
      foreach($products as $product){
        $productEntity=$repositoryProduct->findOneBy(["id"=>$product]);
        $productEntity->priceCalculated($doctrine);
        $productEntity->calculateIncrementByProduct();
        $productEntity->calculateCustomerIncrementByProduct();
        $em->persist($productEntity);
        $em->flush();

      }
    }

    public function getDiscount1(): ?float
    {
        return $this->discount1;
    }

    public function setDiscount1(float $discount1): self
    {
        $this->discount1 = $discount1;

        return $this;
    }

    public function getDiscount2(): ?float
    {
        return $this->discount2;
    }

    public function setDiscount2(?float $discount2): self
    {
        $this->discount2 = $discount2;

        return $this;
    }

    public function getDiscount3(): ?float
    {
        return $this->discount3;
    }

    public function setDiscount3(?float $discount3): self
    {
        $this->discount3 = $discount3;

        return $this;
    }

    public function getDiscount4(): ?float
    {
        return $this->discount4;
    }

    public function setDiscount4(?float $discount4): self
    {
        $this->discount4 = $discount4;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): self
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

}
