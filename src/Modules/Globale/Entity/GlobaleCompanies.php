<?php

namespace App\Modules\Globale\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Modules\ERP\Entity\ERPBankAccounts;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobaleDiskUsages;
use \App\Modules\Globale\Entity\GlobaleAgents;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleCompaniesRepository")
 */
class GlobaleCompanies
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $vat;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $socialname;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=70, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=125, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $postcode;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $mobile;

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
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\GlobaleUserGroups", mappedBy="company")
     */
    private $userGroups;
  	public $newSeconds=1296000;
  	public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCountries", inversedBy="companies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\GlobaleUsers", mappedBy="company")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCurrencies", inversedBy="companies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\GlobaleMenuOptions", mappedBy="company")
     */
    private $menuOptions;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\GlobaleDiskUsages", mappedBy="companyown", fetch="EAGER")
     */
    private $diskUsages;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $domain;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $deviceuser;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $devicepassword;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $ss;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPBankAccounts" , fetch="EAGER")
     */
    private $bankaccount;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleAgents")
     */
    private $agent;


	public function __construct()
    {
        $this->userGroups = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->menuOptions = new ArrayCollection();
        $this->dateadd = new \Datetime();
        $this->dateupd =  new \Datetime();
    }

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

    public function setName(?string $name): self
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

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

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

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

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

    public function getCountry(): ?GlobaleCountries
    {
        return $this->country;
    }

    public function setCountry(?GlobaleCountries $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection|Users[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(GlobaleUsers $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(GlobaleUsers $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
    }

    public function getParameters(){

        return get_class_vars(get_class($this));
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

    /**
     * @return Collection|MenuOptions[]
     */
    public function getMenuOptions(): Collection
    {
        return $this->menuOptions;
    }

    /**
     * @return Collection|MenuOptions[]
     */
    public function getDiskUsages(): Collection
    {
        return $this->diskUsages;
    }

    public function addMenuOption(GlobaleMenuOptions $menuOption): self
    {
        if (!$this->menuOptions->contains($menuOption)) {
            $this->menuOptions[] = $menuOption;
            $menuOption->setCompany($this);
        }

        return $this;
    }

    public function removeMenuOption(GlobaleMenuOptions $menuOption): self
    {
        if ($this->menuOptions->contains($menuOption)) {
            $this->menuOptions->removeElement($menuOption);
            // set the owning side to null (unless already changed)
            if ($menuOption->getCompany() === $this) {
                $menuOption->setCompany(null);
            }
        }

        return $this;
    }

    public function encodeJson (){
      $tempArray = array();
      $vars = get_object_vars ( $this );
      foreach( $vars as $key=>$var){
          if(is_object($var)){
            if(get_class($var)=="DateTime"){
                $tempArray[$key."-date"] = $var->format('d/m/Y');
                $tempArray[$key."-time"] = $var->format('H:i:s');;
            }else{
              if(method_exists($var,"getId")){
                $tempArray[$key] = $var->getId();
              }
            }
          }else{
            $tempArray[$key] = $var;
          }
      }
      return $tempArray;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function preProccess($kernel, $doctrine, $user){
      //Set device user and device Password
      $password = '';

          for($i = 0; $i < 10; $i++) {
              $password .= mt_rand(0, 9);
          }
      if($this->deviceuser==null) $this->deviceuser=time();
      if($this->devicepassword==null) $this->devicepassword=$password;

    }

    public function postProccess($kernel, $doctrine, $user){
      //Prepare folder structure
      $source = $kernel->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'0';
      $dest= $kernel->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->id;
      if(!file_exists($dest)){
        mkdir($dest, 0774);
        foreach (
         $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
          \RecursiveIteratorIterator::SELF_FIRST) as $item
          ) {
           if ($item->isDir()) {
              mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
          }
      }
      //Rename default company imagen
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'large.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.$this->id.'-large.png');
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'medium.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.$this->id.'-medium.png');
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'small.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.$this->id.'-small.png');
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'thumb.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.$this->id.'-thumb.png');
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.'large.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.$this->id.'-large.png');
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.'medium.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.$this->id.'-medium.png');
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.'small.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.$this->id.'-small.png');
      rename($dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.'thumb.png',$dest.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'companydark'.DIRECTORY_SEPARATOR.$this->id.'-thumb.png');
      //Create user admin of the company if it doesn't exist
     $usersrepository=$doctrine->getRepository(GlobaleUsers::class);
     $users=$usersrepository->findBy(["company"=>$this]);
     $create=true;
     foreach($users as $user){
       if(array_search("ROLE_ADMIN",$user->getRoles())!==FALSE){
        $create=false;
        break;
       }
     }
     if($create){

       //Create Admin User
       $user=new GlobaleUsers();
       $user->setName("Administrador");
       $user->setEmail("admin@".$this->getDomain());
       $user->setCompany($this);
       $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
       $user->setPassword('$2y$13$g06ZTdo4bZ6UT3uybO3zjuyB2WPiM.Zxiut3dKU9HGn2A7xC4AdKK');
       $user->setActive(1);
       $user->setDeleted(0);
       $user->setDateadd(new \DateTime());
       $user->setDateupd(new \DateTime());
       $doctrine->getManager()->persist($user);
       $doctrine->getManager()->flush();

       //Create disk quota
       $quota=new GlobaleDiskUsages();
       $quota->setCompany($this);
       $quota->setDiskspace(50);
       $quota->setDiskusage(0);
       $quota->setDistribution("[]");
       $quota->setActive(1);
       $quota->setDeleted(0);
       $quota->setDateadd(new \DateTime());
       $quota->setDateupd(new \DateTime());
       $doctrine->getManager()->persist($quota);
       $doctrine->getManager()->flush();

     }
    }

    public function getDeviceuser(): ?string
    {
        return $this->deviceuser;
    }

    public function setDeviceuser(?string $deviceuser): self
    {
        $this->deviceuser = $deviceuser;

        return $this;
    }

    public function getDevicepassword(): ?string
    {
        return $this->devicepassword;
    }

    public function setDevicepassword(?string $devicepassword): self
    {
        $this->devicepassword = $devicepassword;

        return $this;
    }

    public function getSs(): ?string
    {
        return $this->ss;
    }

    public function setSs(?string $ss): self
    {
        $this->ss = $ss;

        return $this;
    }

    public function getBankaccount(): ?ERPBankAccounts
    {
        return $this->bankaccount;
    }

    public function setBankaccount(?ERPBankAccounts $bankaccount): self
    {
        $this->bankaccount = $bankaccount;

        return $this;
    }

    public function getAgent(): ?GlobaleAgents
    {
        return $this->agent;
    }

    public function setAgent(?GlobaleAgents $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

}
