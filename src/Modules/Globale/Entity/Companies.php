<?php

namespace App\Modules\Globale\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\CompaniesRepository")
 */
class Companies
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
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\UserGroups", mappedBy="company")
     */
    private $userGroups;
	public $newSeconds=1296000;
	public $updatedSeconds=1296000;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\Countries", inversedBy="companies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\Users", mappedBy="company")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\Currencies", inversedBy="companies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\MenuOptions", mappedBy="company")
     */
    private $menuOptions;
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

    public function getCountry(): ?Countries
    {
        return $this->country;
    }

    public function setCountry(?Countries $country): self
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

    public function addUser(Users $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
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

    public function getCurrency(): ?Currencies
    {
        return $this->currency;
    }

    public function setCurrency(?Currencies $currency): self
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

    public function addMenuOption(MenuOptions $menuOption): self
    {
        if (!$this->menuOptions->contains($menuOption)) {
            $this->menuOptions[] = $menuOption;
            $menuOption->setCompany($this);
        }

        return $this;
    }

    public function removeMenuOption(MenuOptions $menuOption): self
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

}
