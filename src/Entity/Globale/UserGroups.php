<?php

namespace App\Entity\Globale;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Globale\UserGroupsRepository")
 */
class UserGroups
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Globale\Users", mappedBy="usergroups")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Globale\Companies", inversedBy="userGroups")
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Globale\Notifications", mappedBy="usergroup")
     */
    private $notifications;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Globale\Users", mappedBy="usergroups")
     */
    private $usersborrar;

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

    public function addUser(Users $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addUsergroup($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeUsergroup($this);
        }

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
            $notification->setUsergroup($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): self
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

    public function addUsersborrar(Users $usersborrar): self
    {
        if (!$this->usersborrar->contains($usersborrar)) {
            $this->usersborrar[] = $usersborrar;
            $usersborrar->addUsergroup($this);
        }

        return $this;
    }

    public function removeUsersborrar(Users $usersborrar): self
    {
        if ($this->usersborrar->contains($usersborrar)) {
            $this->usersborrar->removeElement($usersborrar);
            $usersborrar->removeUsergroup($this);
        }

        return $this;
    }
}
