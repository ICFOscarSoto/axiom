<?php

namespace App\Modules\HR\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobaleCountries;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\HR\Entity\HRDepartments;
use \App\Modules\HR\Entity\HRWorkCenters;
use \App\Modules\HR\Entity\HRWorkCalendarGroups;
use \App\Modules\HR\Entity\HRSchedules;
use \App\Modules\HR\Entity\HRShifts;
use \App\Helpers\HelperValidators;
use \App\Modules\HR\Helpers\HelperAsterisk;
/**
 * @ORM\Entity(repositoryClass="App\Modules\HR\Repository\HRWorkerRepository")
 */
class HRWorkers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=14)
     */
    private $idcard;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $ss;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $lastname;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $external;

    /**
     * @ORM\OneToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status=1;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofemploy;

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
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCountries")
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $bank;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $ccc;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $iban;

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

    public $newSeconds=1296000;
  	public $updatedSeconds=1296000;

   /**
    * @ORM\Column(type="string", length=120, nullable=true)
    */
   private $clockCode;

   /**
    * @ORM\Column(type="boolean", nullable=true)
    */
   private $allowremoteclock;

   /**
    * @ORM\Column(type="datetime", nullable=true)
    */
   private $birthdate;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRDepartments")
    * @ORM\JoinColumn(onDelete="SET NULL")
    */
   private $department;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRWorkCenters")
    * @ORM\JoinColumn(onDelete="SET NULL")
    */
   private $workcenters;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRWorkCalendarGroups")
    */
   private $workcalendargroup;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRSchedules")
    */
   private $schedule;

   /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRShifts")
    */
   private $shift;

   /**
    * @ORM\Column(type="string", length=4, nullable=true)
    */
   private $extension;

   /**
    * @ORM\Column(type="string", length=24, nullable=true)
    */
   private $voippass;

   /**
    * @ORM\Column(type="boolean")
    */
   private $voipregister;

   /**
    * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRProfiles")
    */
   private $profile;

   /**
    * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRProfiles")
    */
   private $profile2;

   /**
    * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRProfiles")
    */
   private $profile3;

   /**
    * @ORM\ManyToOne(targetEntity="App\Modules\HR\Entity\HRProfiles")
    */
   private $profile4;

   /**
    * @ORM\Column(type="string", length=128, nullable=true)
    */
   private $asteriskqueues;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdcard(): ?string
    {
        return $this->idcard;
    }

    public function setIdcard(string $idcard): self
    {
        $this->idcard = $idcard;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getExternal(): ?bool
    {
        return $this->external;
    }

    public function setExternal(?bool $external): self
    {
        $this->external = $external;

        return $this;
    }

    public function getUser(): ?GlobaleUsers
    {
        return $this->user;
    }

    public function setUser(?GlobaleUsers $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateofemploy(): ?\DateTimeInterface
    {
        return $this->dateofemploy;
    }

    public function setDateofemploy(?\DateTimeInterface $dateofemploy): self
    {
        $this->dateofemploy = $dateofemploy;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function setBank(?string $bank): self
    {
        $this->bank = $bank;

        return $this;
    }

    public function getCcc(): ?string
    {
        return $this->ccc;
    }

    public function setCcc(?string $ccc): self
    {
        $this->ccc = $ccc;

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

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

    public function getClockCode(): ?string
    {
        return $this->clockCode;
    }

    public function setClockCode(?string $clockCode): self
    {
        $this->clockCode = $clockCode;

        return $this;
    }

    public function getAllowremoteclock(): ?bool
    {
        return $this->allowremoteclock;
    }

    public function setAllowremoteclock(?bool $allowremoteclock): self
    {
        $this->allowremoteclock = $allowremoteclock;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getDepartment(): ?HRDepartments
    {
        return $this->department;
    }

    public function setDepartment(?HRDepartments $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){
      //$this->time = $this->calculateTime();
      $this->clockCode='CC'.$this->getId();
      $em=$doctrine->getManager();
      $em->persist($this);
      $em->flush();

      //Changed queues or $voipregister???
      if($this->asteriskqueues!=$oldobj->getAsteriskqueues() || $this->voipregister!=$oldobj->getVoipregister()){
        HelperAsterisk::registerWorker($this);
      }

    }

    public function preProccess($kernel, $doctrine, $user, $params, $oldobj){
      //Changed queues or $voipregister???
      if($this->asteriskqueues!=$oldobj->getAsteriskqueues() || $this->voipregister!=$oldobj->getVoipregister()){
        HelperAsterisk::unregisterWorker($oldobj);
      }

    }

    public function getWorkcenters(): ?HRWorkCenters
    {
        return $this->workcenters;
    }

    public function setWorkcenters(?HRWorkCenters $workcenters): self
    {
        $this->workcenters = $workcenters;

        return $this;
    }

    public function getWorkcalendargroup(): ?HRWorkCalendarGroups
    {
        return $this->workcalendargroup;
    }

    public function setWorkcalendargroup(?HRWorkCalendarGroups $workcalendargroup): self
    {
        $this->workcalendargroup = $workcalendargroup;

        return $this;
    }

    public function getSchedule(): ?HRSchedules
    {
        return $this->schedule;
    }

    public function setSchedule(?HRSchedules $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getShift(): ?HRShifts
    {
        return $this->shift;
    }

    public function setShift(?HRShifts $shift): self
    {
        $this->shift = $shift;

        return $this;
    }

    public function formValidation($kernel, $doctrine, $user, $validationParams){
      $repository=$doctrine->getRepository(HRWorkers::class);
      $obj=$repository->findOneBy(["idcard"=>$this->idcard,"company"=>$user->getCompany(),"deleted"=>0]);
      if($obj!=null && $obj->id!=$this->id)
        return ["valid"=>false, "global_errors"=>["El trabajador ya existe"]];
      else {

        //Check CIF/NIF/NIE
        $fieldErrors=[];
        $validator=new HelperValidators();
        if(!$validator->isValidIdNumber($this->idcard)) {$fieldErrors=["idcard"=>"CIF/NIF/NIE no v??lido"]; }
        if($this->email!=null && !$validator->isValidEmail($this->email)) {$fieldErrors=["email"=>"Direcci??n de email no v??lida"]; }
        if($this->iban!=null && !$validator->isValidIban($this->iban)) {$fieldErrors=["iban"=>"Formato de IBAN incorrecto"]; }
        return ["valid"=>empty($fieldErrors), "field_errors"=>$fieldErrors];
      }
    }


    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getVoippass(): ?string
    {
        return $this->voippass;
    }

    public function setVoippass(?string $voippass): self
    {
        $this->voippass = $voippass;

        return $this;
    }

    public function getVoipregister(): ?bool
    {
        return $this->voipregister;
    }

    public function setVoipregister(bool $voipregister): self
    {
        $this->voipregister = $voipregister;

        return $this;
    }

    public function getProfile(): ?HRProfiles
    {
        return $this->profile;
    }

    public function setProfile(?HRProfiles $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getProfile2(): ?HRProfiles
    {
        return $this->profile2;
    }

    public function setProfile2(?HRProfiles $profile2): self
    {
        $this->profile2 = $profile2;

        return $this;
    }

    public function getProfile3(): ?HRProfiles
    {
        return $this->profile3;
    }

    public function setProfile3(?HRProfiles $profile3): self
    {
        $this->profile3 = $profile3;

        return $this;
    }

    public function getProfile4(): ?HRProfiles
    {
        return $this->profile4;
    }

    public function setProfile4(?HRProfiles $profile4): self
    {
        $this->profile4 = $profile4;

        return $this;
    }

    public function getAsteriskqueues(): ?string
    {
        return $this->asteriskqueues;
    }

    public function setAsteriskqueues(?string $asteriskqueues): self
    {
        $this->asteriskqueues = $asteriskqueues;

        return $this;
    }
}
