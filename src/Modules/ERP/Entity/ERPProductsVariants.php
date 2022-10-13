<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\ERP\Entity\ERPVariants;
use \App\Modules\ERP\Utils\ERPPrestashopUtils;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPProductsVariantsRepository")
 */
class ERPProductsVariants
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPVariants")
     * @ORM\JoinColumn(nullable=true)
     */
    private $variant;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $weight=0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purchasepacking=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

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

    public function getProduct(): ?ERPProducts
    {
        return $this->product;
    }

    public function setProduct(?ERPProducts $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getVariant(): ?ERPVariants
    {
        return $this->variant;
    }

    public function setVariant(?ERPVariants $variant): self
    {
        $this->variant = $variant;

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

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getPurchasepacking(): ?int
    {
        return $this->purchasepacking;
    }

    public function setPurchasepacking(?int $purchasepacking): self
    {
        $this->purchasepacking = $purchasepacking;

        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $params){
      $result                   = ["valid"=>true];
      $productVariantRepository = $doctrine->getRepository(ERPProductsVariants::class);

      // Se comprueba que no este duplicado
      $productvariant = $productVariantRepository->findOneBy(["product"=>$this->getProduct(), "variant"=>$this->getVariant(), "deleted"=>0]);
      if ($productvariant && $productvariant->getId()!=$this->getId()){
        // Errores de todo el formulario
        $result=["valid"=>false, "global_errors"=>["Variante duplicada"]];
      }
      // Validación a nivel de campo
      //$result=["valid"=>false, "field_error"=>["dni"=>"Ejemplo DNI incorrecto"]];
      return $result;
    }

    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){
      $array_new_data = [];
      if ($this->getVariant()){
        /*$array_new_data["variants"]["type"]=$this->getVariant()->getVarianttype()->getName();
        $array_new_data["variants"]["new"]=$this->getVariant()->getName();
        if($oldobj->getVariant()) $array_new_data["variants"]["old"]=$oldobj->getVariant()->getName();
        else $array_new_data["variants"]["old"]=null;

        $webproductRepository=$doctrine->getRepository(ERPWebProducts::class);
        $webproduct=$webproductRepository->findOneBy(["product"=>$this->getProduct()]);

        $prestashopUtils= new ERPPrestashopUtils();
        $prestashopUtils->updateWebProduct($doctrine,$array_new_data,$this->getProduct(),$webproduct);*/
      }
    }
}
