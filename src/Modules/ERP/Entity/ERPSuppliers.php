<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\Globale\Entity\GlobaleActivities;
use \App\Modules\Globale\Entity\GlobaleCurrencies;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPPaymentMethods;
use \App\Modules\Globale\Entity\GlobaleStates;
use \App\Modules\ERP\Entity\ERPPaymentTerms;
use \App\Modules\ERP\Entity\ERPSupplierActivities;
use \App\Modules\Carrier\Entity\CarrierCarriers;
use \App\Modules\Carrier\Entity\CarrierShippingConditions;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSuppliersRepository")
 */
class ERPSuppliers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $vat;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $socialname;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=70)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $postcode;

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
    private $deleted;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $web;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleActivities")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $activity;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCurrencies")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     *
     */
    private $taxexection;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentMethods")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $paymentmethod;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $creditor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoiceday;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $theircode;

    /**
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    private $credentialsurl;
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $credentialsuser;
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $credentialspassword;

    public $newSeconds=1296000;
    public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleStates")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPPaymentTerms")
     */
    private $paymentterms;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSupplierActivities", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $workactivity;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setVat(string $vat): self
    {
        $this->vat = $vat;

        return $this;
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

    public function getSocialname(): ?string
    {
        return $this->socialname;
    }

    public function setSocialname(string $socialname): self
    {
        $this->socialname = $socialname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?GlobaleCountries
    {
        return $this->country;
    }

    public function setCountry(?GlobaleCountries $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function setWeb(?string $web): self
    {
        $this->web = $web;

        return $this;
    }

    public function getActivity(): ?GlobaleActivities
    {
        return $this->activity;
    }

    public function setActivity(?GlobaleActivities $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function getCurrency(): ?GlobaleCurrencies
    {
        return $this->currency;
    }

    public function setCurrency(?GlobaleCurrencies $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getTaxexection(): ?string
    {
        return $this->taxexection;
    }

    public function setTaxexection(?string $taxexection): self
    {
        $this->taxexection = $taxexection;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

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

    public function getPaymentmethod(): ?ERPPaymentMethods
    {
        return $this->paymentmethod;
    }

    public function setPaymentmethod(?ERPPaymentMethods $paymentmethod): self
    {
        $this->paymentmethod = $paymentmethod;

        return $this;
    }

    public function getCreditor(): ?bool
    {
        return $this->creditor;
    }

    public function setCreditor(?bool $creditor): self
    {
        $this->creditor = $creditor;

        return $this;
    }

    public function getInvoiceday(): ?int
    {
        return $this->invoiceday;
    }

    public function setInvoiceday(?int $invoiceday): self
    {
        $this->invoiceday = $invoiceday;

        return $this;
    }

    public function getState(): ?GlobaleStates
    {
        return $this->state;
    }

    public function setState(?GlobaleStates $state): self
    {
        $this->state = $state;

        return $this;
    }


    public function formValidation($kernel, $doctrine, $user, $validationParams){
      $fieldErrors=[];
      if($this->country==null){
        $fieldErrors["country"]="This field is required.";
      }
      if($this->state==null){
        $fieldErrors["state"]="This field is required.";
      }
      if($this->currency==null){
        $fieldErrors["currency"]="This field is required.";
      }
      if($this->paymentmethod==null){
        $fieldErrors["paymentmethod"]="This field is required.";
      }

      if (empty($fieldErrors)) return ["valid"=>true];
        else return ["valid"=>false, "field_errors"=>$fieldErrors];
    }

    public function getPaymentterms(): ?ERPPaymentTerms
    {
        return $this->paymentterms;
    }

    public function setPaymentterms(?ERPPaymentTerms $paymentterms): self
    {
        $this->paymentterms = $paymentterms;

        return $this;
    }

    public function getWorkactivity(): ?ERPSupplierActivities
    {
        return $this->workactivity;
    }

    public function setWorkactivity(?ERPSupplierActivities $workactivity): self
    {
        $this->workactivity = $workactivity;

        return $this;
    }

    public function getTheircode(): ?string
    {
        return $this->theircode;
    }

    public function setTheircode(string $theircode): self
    {
        $this->theircode = $theircode;

        return $this;
    }

    public function getCredentialsurl(): ?string
    {
        return $this->credentialsurl;
    }

    public function setCredentialsurl(string $credentialsurl): self
    {
        $this->credentialsurl = $credentialsurl;

        return $this;
    }

    public function getCredentialsuser(): ?string
    {
        return $this->credentialsuser;
    }

    public function setCredentialsuser(string $credentialsuser): self
    {
        $this->credentialsuser = $credentialsuser;

        return $this;
    }

    public function getCredentialspassword(): ?string
    {
        return $this->credentialspassword;
    }

    public function setCredentialspassword(string $credentialspassword): self
    {
        $this->credentialspassword = $credentialspassword;

        return $this;
    }
  /*
    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){



            //hay que crear los objetos de las entidades dependientes de supplier cuando se crea un supplier por primera vez


             $repositoryProduct=$doctrine->getRepository(ERPProducts::class);
             $repositoryWebProduct=$doctrine->getRepository(ERPWebProducts::class);
             $product_ids=$repositoryProduct->getWebProductBySupplier($this);
             foreach($product_ids as $product_id){

               $product=$repositoryProduct->findOneBy(["id"=>$product_id]);
               $webproduct=$repositoryWebProduct->findOneBy(["product"=>$product]);
               $prestashopUtils= new ERPPrestashopUtils();
               $prestashopUtils->updateWebProductPrices($doctrine,$product,$webproduct);

               if($oldobj->getEstimateddelivery()!=$this->getEstimateddelivery()){
                 $plazo_entrega=null;
                 if($this->getEstimateddelivery()) $plazo_entrega=$this->getEstimateddelivery()+4;
                 else $plazo_entrega="-1";
                 $array_new_data = [];
                 $array_new_data["estimateddelivery"]=$plazo_entrega;

                 $prestashopUtils= new ERPPrestashopUtils();
                 $prestashopUtils->updateWebProduct($doctrine,$array_new_data,$product,$webproduct);
              }


           }


   }
*/


}
