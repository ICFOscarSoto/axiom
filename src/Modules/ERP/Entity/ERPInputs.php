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

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : false})
     */
    private $navinput=false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $navauthor;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $ourcode;

    private $url="http://192.168.1.250:9000/";

    public function __construct()
    {
      //$this->date=new \DateTime();
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

    //Called automatically when a file is uploaded or scanned
    public function postUploadCloudFile($cloudFile, $kernel, $doctrine, $user){
      if($cloudFile->getCompany()->getId()==2 && $cloudFile->getType()=="Albar??n Proveedor" && $this->inputdate!=""){
        sleep(1);
        $this->discordNotify($cloudFile);
        //Procesar OCR en el fichero si es PDF
        $dir=$kernel->getRootDir() . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$user->getCompany()->getId().DIRECTORY_SEPARATOR.$cloudFile->getPath().DIRECTORY_SEPARATOR.$this->id.DIRECTORY_SEPARATOR;
        $extension = pathinfo($cloudFile->getName(), PATHINFO_EXTENSION);
        if(strtolower($extension)=='pdf') $result=exec("nohup ocrmypdf -l spa -r --force-ocr --rotate-pages-threshold 5 \"".$dir.$cloudFile->getHashname()."\" \"".$dir.$cloudFile->getHashname()."\" >/dev/null 2>&1 &");
      }
    }

    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){
      $cloudRepository=$doctrine->getRepository(CloudFiles::class);
      $repositoryUsers=$doctrine->getRepository(GlobaleUsers::class);
      $files=$cloudRepository->findBy(["company"=>$user->getCompany(), "path"=>"ERPInputs", "idclass"=>$this->id, "type"=>"Albar??n Proveedor"]);
      if(count($files)>0 && $this->inputdate!="" && $oldobj->getInputdate()==""){
        $this->discordNotify($files[0]);
      }
      //Ourcode is changed? check it with Navision
      if($this->ourcode!=null && $this->ourcode!=$oldobj->getOurcode()){
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPurchaseDeliveryExistsbyNo.php?code='.urlencode($this->ourcode).'&supplier='.urlencode($this->supplier->getCode()).'&newcode='.urlencode($this->getCode()));
        //dump($this->url.'navisionExport/axiom/do-NAVISION-getPurchaseDeliveryExistsbyNo.php?code='.urlencode($this->ourcode).'&supplier='.urlencode($this->supplier->getCode()).'&newcode='.urlencode($this->getCode()));
        $object=json_decode($json, true);
        //dump($object);
        if(json_last_error() == JSON_ERROR_NONE && $object["result"]==1){
            $user=$repositoryUsers->findOneBy(["email"=>$object["data"]["author"]]);
            $this->setNavinput(true);
            $this->setNavauthor($user);
            $doctrine->getManager()->persist($this);
            $doctrine->getManager()->flush();
        }else{
            $this->setOurcode(null);
            $doctrine->getManager()->persist($this);
            $doctrine->getManager()->flush();
        }

      }



    }

    public function discordNotify($cloudFile){
        $channel='829033245332996106';

        $msg="Nueva entrada albar??n N?? **".$this->code."** de **".$this->supplier->getName()."**";
        if($this->store!=null) $msg=$msg." en ".$this->store->getName();
        file_get_contents('https://icfbot.ferreteriacampollano.com/file.php?channel='.$channel.'&msg='.urlencode($msg).'&file='.urlencode('/var/www/axiom.ferreteriacampollano.com/cloud/2/'.$cloudFile->getPath().'/'.$cloudFile->getIdclass().'/'.$cloudFile->getHashname()).'&filename='.urlencode($cloudFile->getName()));
        if($this->comments!=""){
          $msg="**Comentarios:** \n".strip_tags($this->comments);
          file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
        }
        $msg="\n\nMas info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/inputs/form/'.$this->id;
        file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
    }

    public function formValidation($controller, $doctrine, $user, $validationParams){
      $repository=$doctrine->getRepository(ERPInputs::class);

      /*if($this->id!=null)
        $obj=$repository->find($this->id);
        else $obj=null;*/

      $global_errors=[];
      $input=$repository->findCleanCode($this->code, $this->supplier, $user);
      if($input!=null && $input['id']!=$this->id) $global_errors[]="El albar??n ".$input['code']." ya existe para el proveedor ".$this->supplier->getName().". Puede visualizarlo y modificarlo haciendo click <a href='/es/ERP/inputs/form/".$input['id']."'>aqu??.</a>";

      if($this->ourcode!=null ){
        $input=$repository->findOurCleanCode($this->ourcode, $user);
        if($input!=null && $input['id']!=$this->id) $global_errors[]="Nuestro n??mero de albar??n ".$input['ourcode']." ya esta asignado a otro albar??n de proveedor";
      }

      if(count($global_errors)==0) return ["reload"=>true, "valid"=>true];
        else return ["valid"=>false, "global_errors"=>$global_errors];

    }

    public function getNavinput(): ?bool
    {
        return $this->navinput;
    }

    public function setNavinput(?bool $navinput): self
    {
        $this->navinput = $navinput;

        return $this;
    }

    public function getNavauthor(): ?GlobaleUsers
    {
        return $this->navauthor;
    }

    public function setNavauthor(?GlobaleUsers $navauthor): self
    {
        $this->navauthor = $navauthor;

        return $this;
    }

    public function getOurcode(): ?string
    {
        return $this->ourcode;
    }

    public function setOurcode(?string $ourcode): self
    {
        $this->ourcode = $ourcode;

        return $this;
    }



}
