<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPProducts;
use \App\Modules\Globale\Entity\GlobaleCompanies;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPWebProductsRepository")
 */
class ERPWebProducts
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="\App\Modules\ERP\Entity\ERPProducts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

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
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $metatitle;

    /**
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $metadescription;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $additionalcost;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minquantityofsaleweb;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $equivalence;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $measurementunityofequivalence;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $webprice;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?ERPProducts
    {
        return $this->product;
    }

    public function setProduct(ERPProducts $product): self
    {
        $this->product = $product;

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

    public function getMetatitle(): ?string
    {
        return $this->metatitle;
    }

    public function setMetatitle(?string $metatitle): self
    {
        $this->metatitle = $metatitle;

        return $this;
    }

    public function getMetadescription(): ?string
    {
        return $this->metadescription;
    }

    public function setMetadescription(?string $metadescription): self
    {
        $this->metadescription = $metadescription;

        return $this;
    }

    public function getAdditionalcost(): ?float
    {
        return $this->additionalcost;
    }

    public function setAdditionalcost(?float $additionalcost): self
    {
        $this->additionalcost = $additionalcost;

        return $this;
    }

    public function getMinquantityofsaleweb(): ?int
    {
        return $this->minquantityofsaleweb;
    }

    public function setMinquantityofsaleweb(?int $minquantityofsaleweb): self
    {
        $this->minquantityofsaleweb = $minquantityofsaleweb;

        return $this;
    }

    public function getEquivalence(): ?float
    {
        return $this->equivalence;
    }

    public function setEquivalence(?float $equivalence): self
    {
        $this->equivalence = $equivalence;

        return $this;
    }

    public function getMeasurementunityofequivalence(): ?string
    {
        return $this->measurementunityofequivalence;
    }

    public function setMeasurementunityofequivalence(?string $measurementunityofequivalence): self
    {
        $this->measurementunityofequivalence = $measurementunityofequivalence;

        return $this;
    }
  /*
    public function formValidation($kernel, $doctrine, $user, $validationParams){
      if($this->measurementunityofequivalence!=NULL and (int)$this->equivalence=="0")
          //return ["valid"=>false, "global_errors"=>["Tiene que indicar un valor para la equivalencia"]];
      }
      */

    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){
      $this->updateWebProduct($doctrine,$oldobj);

    }

    public function updateWebProduct($doctrine,$oldobj){
       $array_new_data=[];
       foreach($oldobj as $clave=>$valor){

         if($oldobj->$clave!=$this->$clave AND $clave!="dateupd"){
          if($clave=="measurementunityofequivalence"){
            if($this->$clave=="0") $array_new_data[$clave]="unidad";
            else if($this->$clave=="1") $array_new_data[$clave]="metro";
            else if($this->$clave=="2") $array_new_data[$clave]="kilo";
            else if($this->$clave=="3") $array_new_data[$clave]="litro";
            else if($this->$clave=="4") $array_new_data[$clave]="metro cuadrado";
          }

          else $array_new_data[$clave]=$this->$clave;

         }
       }

       //se ha modificado algún valor, luego hay que actualizarlo en la web
       if($array_new_data!=[]) {


         $this_url="https://www.ferreteriacampollano.com";
         $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
         $context = stream_context_create([
             "http" => ["header" => "Authorization: Basic $auth"]
         ]);

         try{
              //OBTENER ID DEL PRODUCTO EN prestashopGetProduct
              dump($this->getProduct()->getCode());
              $xml_string=file_get_contents($this_url."/api/products/?filter[reference]=".$this->getProduct()->getCode(), false, $context);
              $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
              $id_prestashop=$xml->products->product['id'];
              dump($id_prestashop);
              //actualizamos prestashop
               $xml_string=file_get_contents($this_url."/api/products/".$id_prestashop, false, $context);
              // $xml_string=file_get_contents($this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC", false, $context);
               $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
            //   $xml->product->cantidad_pedido_minimo="3";

               unset($xml->product->manufacturer_name);
               unset($xml->product->quantity);

               $repositotyPrestashopFieldNames=$doctrine->getRepository(ERPPrestashopFieldNames::class);
               foreach($array_new_data as $clave=>$valor)
               {
                 $PrestashopFieldName=$repositotyPrestashopFieldNames->findOneBy(["axiomname"=>$clave]);
                 if($PrestashopFieldName!=NULL){
                    $psname=$PrestashopFieldName->getPrestashopname();
                    if($xml->product->$psname->language) {;
                      $xml->product->$psname->language=$valor;
                    }
                    else $xml->product->$psname=$valor;
                  }
               }

                $url = "https://www.ferreteriacampollano.com/api/products/".$id_prestashop;
              //  $url= $this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC";
                $ch = curl_init();

                $putString = $xml->asXML();
                //dump($putString);
                /** use a max of 256KB of RAM before going to disk */
                $putData = fopen('php://temp/maxmemory:256000', 'w');
                if (!$putData) {
                    die('could not open temp memory data');
                }
                fwrite($putData, $putString);
                fseek($putData, 0);

                // Headers
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.$auth));
                // Binary transfer i.e. --data-BINARY
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                // Using a PUT method i.e. -XPUT
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));

                curl_setopt($ch, CURLOPT_INFILE, $putData);

                $output = curl_exec($ch);

                // Close the file
                fclose($putData);
                // Stop curl
            //    curl_close($ch);

                if (curl_errno($ch)) {  dump(curl_error($ch)); }
                else {  curl_close($ch); }  // $data contains the result of the post...


              }catch(Exception $e){}





      }



       }

    public function getWebprice(): ?float
    {
        return $this->webprice;
    }

    public function setWebprice(?float $webprice): self
    {
        $this->webprice = $webprice;

        return $this;
    }


}
