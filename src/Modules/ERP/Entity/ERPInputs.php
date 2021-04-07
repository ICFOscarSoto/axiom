<?php

namespace App\Modules\ERP\Entity;

use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPCarriers;
use \App\Modules\ERP\Entity\ERPSuppliers;
use \App\Modules\ERP\Entity\ERPStoreLocations;
use \App\Modules\ERP\Entity\ERPStores;
use \App\Modules\Cloud\Entity\CloudFiles;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\ERP\Entity\ERPInputsRepository")
 */
class ERPInputs
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCarriers")
     */
    private $carrier;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $packages;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSuppliers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreLocations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPStores")
     * @ORM\JoinColumn(nullable=true)
     */
    private $store;

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
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $inputdate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    public function __construct()
    {
      $this->date=new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCarrier(): ?ERPCarriers
    {
        return $this->carrier;
    }

    public function setCarrier(?ERPCarriers $carrier): self
    {
        $this->carrier = $carrier;

        return $this;
    }

    public function getPackages(): ?int
    {
        return $this->packages;
    }

    public function setPackages(int $packages): self
    {
        $this->packages = $packages;

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

    public function getLocation(): ?ERPStoreLocations
    {
        return $this->location;
    }

    public function setLocation(?ERPStoreLocations $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getStore(): ?ERPStores
    {
        return $this->store;
    }

    public function setStore(?ERPStores $store): self
    {
        $this->store = $store;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): self
    {
        $this->comments = $comments;
        return $this;
    }

    //Called automatically when a file is uploaded or scanned
    public function postUploadCloudFile($cloudFile, $doctrine){
      if($cloudFile->getCompany()->getId()==2 && $cloudFile->getType()=="Albarán Proveedor" && $this->inputdate!=""){
        $this->discordNotify($cloudFile);
      }
    }

    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){
      $cloudRepository=$doctrine->getRepository(CloudFiles::class);
      $files=$cloudRepository->findBy(["company"=>$user->getCompany(), "path"=>"ERPInputs", "idclass"=>$this->id, "type"=>"Albarán Proveedor"]);
      if(count($files)>0 && $this->inputdate!="" && $oldobj->getInputdate()==""){
        dump($files);
        $this->discordNotify($files[0]);
      }
    }

    public function discordNotify($cloudFile){
        $channel='829033245332996106';

        $msg="Nueva entrada albarán Nº **".$this->code."** de **".$this->supplier->getName()."**";
        if($this->store!=null) $msg=$msg." en ".$this->store->getName();
        file_get_contents('https://icfbot.ferreteriacampollano.com/file.php?channel='.$channel.'&msg='.urlencode($msg).'&file='.urlencode('/var/www/axiom.ferreteriacampollano.com/cloud/2/'.$cloudFile->getPath().'/'.$cloudFile->getIdclass().'/'.$cloudFile->getHashname()).'&filename='.urlencode($cloudFile->getName()));
        dump('https://icfbot.ferreteriacampollano.com/file.php?channel='.$channel.'&msg='.urlencode($msg).'&file='.urlencode('/var/www/axiom.ferreteriacampollano.com/cloud/2/'.$cloudFile->getPath().'/'.$cloudFile->getIdclass().'/'.$cloudFile->getHashname()).'&filename='.urlencode($cloudFile->getName()));
        if($this->comments!=""){
          $msg="**Comentarios:** \n".strip_tags($this->comments);
          file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
        }
        $msg="\n\nMas info en: \n".'http://devaxiom.ferreteriacampollano.com/es/ERP/inputs/form/'.$this->id;
        file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
    }

    public function getInputdate(): ?\DateTimeInterface
    {
        return $this->inputdate;
    }

    public function setInputdate(?\DateTimeInterface $inputdate): self
    {
        $this->inputdate = $inputdate;

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
}
