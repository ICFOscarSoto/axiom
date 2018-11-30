<?php

namespace App\Entity\Globale;

use App\Modules\Email\Entity\EmailAccounts;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Globale\UsersRepository")
  * @UniqueEntity(fields="email", message="El email ya existe")
 */
class Users implements UserInterface
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
    private $firstname;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Globale\Companies", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Globale\UserGroups", inversedBy="usersborrar")
     */
    private $usergroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Globale\Notifications", mappedBy="users")
     */
    private $notifications;

	/**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

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



    public function __construct()
    {
    $this->usergroups = new ArrayCollection();
    $this->notifications = new ArrayCollection();
    $this->emailAccounts = new ArrayCollection();
    $em = $this->getDoctrine()->getManager();

    $notificationsRepository = $em->getRepository(Notifications::class);
    $notifications=$notificationsRepository->findNoReaded($this->id);
    foreach($notifications as $notification) $this->addNotification($notification);

    $emailRepository = $em->getRepository(EmailAccounts::class);
    $emailAccounts=$emailRepository->findByUserId($this->id);
    foreach($emailAccounts as $emailAccount) $this->addEmailAccount($emailAccount);

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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getCompany(): ?Companies
    {
        return $this->company;
    }

    public function setCompany(?Companies $company): self
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
           		$data["firstname"]=$this->getFirstname();
           		$data["roles"]=$this->getRoles();
           		return $data;
           	}

    /**
     * @return Collection|UserGroups[]
     */
    public function getUsergroups(): Collection
    {
        return $this->usergroups;
    }

    public function addUsergroup(UserGroups $usergroup): self
    {
        if (!$this->usergroups->contains($usergroup)) {
            $this->usergroups[] = $usergroup;
        }

        return $this;
    }

    public function removeUsergroup(UserGroups $usergroup): self
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

    public function addNotification(Notifications $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setUsers($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): self
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

}
