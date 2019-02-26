<?php

namespace App\Modules\Globale\Entity;

use App\Modules\Calendar\Entity\CalendarCalendars;
use App\Modules\Email\Entity\EmailAccounts;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleUsersRepository")
  * @UniqueEntity(fields="email", message="El email ya existe")
 */
class GlobaleUsers implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $lastname;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToMany(targetEntity="App\Modules\Globale\Entity\GlobaleUserGroups", inversedBy="usersborrar")
     */
    private $usergroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\GlobaleNotifications", mappedBy="users")
     */
    private $notifications;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateadd;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateupd;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $active;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $deleted;
	  public $newSeconds=1296000;
	  public $updatedSeconds=1296000;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Email\Entity\EmailAccounts", mappedBy="user", orphanRemoval=true, fetch="EAGER")
     */
    private $emailAccounts;

    /**
     * @ORM\OneToOne(targetEntity="App\Modules\Email\Entity\EmailAccounts", cascade={"persist", "remove"})
     */
    private $emailDefaultAccount;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Calendar\Entity\CalendarCalendars", mappedBy="user" )
     */
    private $calendars;



    public function __construct()
    {
      $this->usergroups = new ArrayCollection();
      $this->notifications = new ArrayCollection();
      $this->emailAccounts = new ArrayCollection();
      }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): ?array
    {
         $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }
	  /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


	public function getTemplateData(){
                                $data["id"]=$this->getId();
                             		$data["email"]=$this->getEmail();
                             		$data["name"]=$this->getName();
                             		$data["firstname"]=$this->getLastname();
                             		$data["roles"]=$this->getRoles();
                                $data["companyId"]=$this->getCompany()->getId();
                             		return $data;
                             	}

    /**
     * @return Collection|UserGroups[]
     */
    public function getUsergroups(): Collection
    {
        return $this->usergroups;
    }

    public function addUsergroup(GlobaleUserGroups $usergroup): self
    {
        if (!$this->usergroups->contains($usergroup)) {
            $this->usergroups[] = $usergroup;
        }

        return $this;
    }

    public function removeUsergroup(GlobaleUserGroups $usergroup): self
    {
        if ($this->usergroups->contains($usergroup)) {
            $this->usergroups->removeElement($usergroup);
        }

        return $this;
    }

    /**
     * @return Collection|Notifications[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(GlobaleNotifications $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUsers($this);
        }

        return $this;
    }

    public function removeNotification(GlobaleNotifications $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getUsers() === $this) {
                $notification->setUsers(null);
            }
        }

        return $this;
    }

    public function getDateadd(): ?\DateTimeInterface
    {
        return $this->dateadd;
    }

    public function setDateadd(?\DateTimeInterface $dateadd): self
    {
        $this->dateadd = $dateadd;

        return $this;
    }

    public function getDateupd(): ?\DateTimeInterface
    {
        return $this->dateupd;
    }

    public function setDateupd(?\DateTimeInterface $dateupd): self
    {
        $this->dateupd = $dateupd;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection|EmailAccounts[]
     */
    public function getEmailAccounts(): Collection
    {
        return $this->emailAccounts;
    }

    public function addEmailAccount(EmailAccounts $emailAccount): self
    {
        if (!$this->emailAccounts->contains($emailAccount)) {
            $this->emailAccounts[] = $emailAccount;
            $emailAccount->setUser($this);
        }

        return $this;
    }

    public function removeEmailAccount(EmailAccounts $emailAccount): self
    {
        if ($this->emailAccounts->contains($emailAccount)) {
            $this->emailAccounts->removeElement($emailAccount);
            // set the owning side to null (unless already changed)
            if ($emailAccount->getUser() === $this) {
                $emailAccount->setUser(null);
            }
        }

        return $this;
    }

    public function getEmailDefaultAccount(): ?EmailAccounts
    {
        return $this->emailDefaultAccount;
    }

    public function setEmailDefaultAccount(?EmailAccounts $emailDefaultAccount): self
    {
        $this->emailDefaultAccount = $emailDefaultAccount;

        return $this;
    }

    /**
     * @return Collection|CalendarCalendars[]
     */
    public function getCalendars(): Collection
    {
        return $this->calendars;
    }

/*
    public function addCalendar(Calendars $calendar): self
    {
        if (!$this->calendars->contains($calendar)) {
            $this->calendars[] = $calendar;
            $calendar->setUser($this);
        }

        return $this;
    }

    public function removeCalendar(Calendars $calendar): self
    {
        if ($this->calendars->contains($calendar)) {
            $this->calendars->removeElement($calendar);
            // set the owning side to null (unless already changed)
            if ($calendar->getUser() === $this) {
                $calendar->setUser(null);
            }
        }

        return $this;
    }*/

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
