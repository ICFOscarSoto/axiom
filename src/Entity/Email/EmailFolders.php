<?php

namespace App\Entity\Email;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Email\EmailFoldersRepository")
 */
class EmailFolders
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Email\EmailAccounts", inversedBy="emailFolders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $emailAccount;

     /**
     * @ORM\OrderBy({"date" = "DESC", "uid" = "DESC"})
     * @ORM\OneToMany(targetEntity="App\Entity\Email\EmailSubjects", mappedBy="folder", fetch="EAGER")
     */
    private $emailSubjects;

    public function __construct()
    {
        $this->emailSubjects = new ArrayCollection();
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

    public function getEmailAccount(): ?EmailAccounts
    {
        return $this->emailAccount;
    }

    public function setEmailAccount(?EmailAccounts $emailAccount): self
    {
        $this->emailAccount = $emailAccount;

        return $this;
    }

    /**
     * @return Collection|EmailSubjects[]
     */
    public function getEmailSubjects(): Collection
    {
        return $this->emailSubjects;
    }

    public function addEmailSubject(EmailSubjects $emailSubject): self
    {
        if (!$this->emailSubjects->contains($emailSubject)) {
            $this->emailSubjects[] = $emailSubject;
            $emailSubject->setFolder($this);
        }

        return $this;
    }

    public function removeEmailSubject(EmailSubjects $emailSubject): self
    {
        if ($this->emailSubjects->contains($emailSubject)) {
            $this->emailSubjects->removeElement($emailSubject);
            // set the owning side to null (unless already changed)
            if ($emailSubject->getFolder() === $this) {
                $emailSubject->setFolder(null);
            }
        }

        return $this;
    }
}
