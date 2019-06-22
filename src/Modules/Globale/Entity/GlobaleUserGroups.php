<?php

namespace App\Modules\Globale\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleUserGroupsRepository")
 */
class GlobaleUserGroups
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $roles = [];

    /**
     * @ORM\ManyToMany(targetEntity="App\Modules\Globale\Entity\GlobaleUsers", mappedBy="usergroups")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies", inversedBy="userGroups")
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Globale\Entity\GlobaleNotifications", mappedBy="usergroup")
     */
    private $notifications;

    /**
     * @ORM\ManyToMany(targetEntity="App\Modules\Globale\Entity\GlobaleUsers", mappedBy="usergroups")
     */
    private $usersborrar;

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

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->usersborrar = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

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
            $user->addUsergroup($this);
        }

        return $this;
    }

    public function removeUser(GlobaleUsers $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeUsergroup($this);
        }

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
            $notification->setUsergroup($this);
        }

        return $this;
    }

    public function removeNotificationGlobale(GlobaleNotifications $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getUsergroup() === $this) {
                $notification->setUsergroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Users[]
     */
    public function getUsersborrar(): Collection
    {
        return $this->usersborrar;
    }

    public function addUsersborrar(GlobaleUsers $usersborrar): self
    {
        if (!$this->usersborrar->contains($usersborrar)) {
            $this->usersborrar[] = $usersborrar;
            $usersborrar->addUsergroup($this);
        }

        return $this;
    }

    public function removeUsersborrar(GlobaleUsers $usersborrar): self
    {
        if ($this->usersborrar->contains($usersborrar)) {
            $this->usersborrar->removeElement($usersborrar);
            $usersborrar->removeUsergroup($this);
        }

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
}
