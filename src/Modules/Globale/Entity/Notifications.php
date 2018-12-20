<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\NotificationsRepository")
 */
class Notifications
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\Users", inversedBy="notifications")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\UserGroups", inversedBy="notifications")
     */
    private $usergroup;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $readed;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\Users", inversedBy="notifications")
     */
    private $users;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

	public $newSeconds=3600;
	public $updatedSeconds=3600;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUsergroup(): ?UserGroups
    {
        return $this->usergroup;
    }

    public function setUsergroup(?UserGroups $usergroup): self
    {
        $this->usergroup = $usergroup;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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

    public function getReaded(): ?bool
    {
        return $this->readed;
    }

    public function setReaded(?bool $readed): self
    {
        $this->readed = $readed;

        return $this;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

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
