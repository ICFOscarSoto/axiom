<?php
//namespace App\Entity\Email;
namespace App\Modules\Email\Entity;

use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Email\Entity\EmailFolders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Email\Repository\EmailAccountsRepository")
 */
class EmailAccounts
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
     * @ORM\Column(type="string", length=150)
     */
    private $server;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $port;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $protocol;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers", inversedBy="emailAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Modules\Email\Entity\EmailFolders", mappedBy="emailAccount", orphanRemoval=true, fetch="EAGER")
     */
    private $emailFolders;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $smtpServer;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $smtpPort;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $smtpUsername;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $smtpPassword;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Email\Entity\EmailFolders", inversedBy="emailAccountsInbox")
     */
    private $inboxFolder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Email\Entity\EmailFolders")
     */
    private $sentFolder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Email\Entity\EmailFolders")
     */
    private $trashFolder;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $signature;

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

    public function __construct()
    {
        $this->emailFolders = new ArrayCollection();
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

    public function getServer(): ?string
    {
        return $this->server;
    }

    public function setServer(string $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    public function setPort(string $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getUser(): ?GlobaleUsers
    {
        return $this->user;
    }

    public function setUser(?GlobaleUsers $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|EmailFolders[]
     */
    public function getEmailFolders(): Collection
    {
        return $this->emailFolders;
    }

    public function addEmailFolder(EmailFolders $emailFolder): self
    {
        if (!$this->emailFolders->contains($emailFolder)) {
            $this->emailFolders[] = $emailFolder;
            $emailFolder->setEmailAccount($this);
        }

        return $this;
    }

    public function removeEmailFolder(EmailFolders $emailFolder): self
    {
        if ($this->emailFolders->contains($emailFolder)) {
            $this->emailFolders->removeElement($emailFolder);
            // set the owning side to null (unless already changed)
            if ($emailFolder->getEmailAccount() === $this) {
                $emailFolder->setEmailAccount(null);
            }
        }

        return $this;
    }

    public function getSmtpServer(): ?string
    {
        return $this->smtpServer;
    }

    public function setSmtpServer(?string $smtpServer): self
    {
        $this->smtpServer = $smtpServer;

        return $this;
    }

    public function getSmtpPort(): ?string
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(?string $smtpPort): self
    {
        $this->smtpPort = $smtpPort;

        return $this;
    }

    public function getSmtpUsername(): ?string
    {
        return $this->smtpUsername;
    }

    public function setSmtpUsername(?string $smtpUsername): self
    {
        $this->smtpUsername = $smtpUsername;

        return $this;
    }

    public function getSmtpPassword(): ?string
    {
        return $this->smtpPassword;
    }

    public function setSmtpPassword(?string $smtpPassword): self
    {
        $this->smtpPassword = $smtpPassword;

        return $this;
    }

    public function getInboxFolder(): ?EmailFolders
    {
        return $this->inboxFolder;
    }

    public function setInboxFolder(?EmailFolders $inboxFolder): self
    {
        $this->inboxFolder = $inboxFolder;

        return $this;
    }

    public function getSentFolder(): ?EmailFolders
    {
        return $this->sentFolder;
    }

    public function setSentFolder(?EmailFolders $sentFolder): self
    {
        $this->sentFolder = $sentFolder;

        return $this;
    }

    public function getTrashFolder(): ?EmailFolders
    {
        return $this->trashFolder;
    }

    public function setTrashFolder(?EmailFolders $trashFolder): self
    {
        $this->trashFolder = $trashFolder;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;

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

    public function postProccess($kernel, $doctrine, $user){
      //Check if exist folders
      /*$em=$doctrine->getManager();
      if($this->getInboxFolder()==null){
        $folder=new EmailFolders();
        $folder->setName("INBOX");
        $folder->setEmailAccount($this);
        $em->persist($folder);
        $em->flush();
        $this->setInboxFolder($folder);
      }
      if($this->getSentFolder()==null){
        $folder=new EmailFolders();
        $folder->setName("Elementos enviados");
        $folder->setEmailAccount($this);
        $em->persist($folder);
        $em->flush();
        $this->setSentFolder($folder);
      }
      if($this->getTrashFolder()==null){
        $folder=new EmailFolders();
        $folder->setName("Papelera");
        $folder->setEmailAccount($this);
        $em->persist($folder);
        $em->flush();
        $this->setTrashFolder($folder);
      }
      $em->persist($this);
      $em->flush();*/

    }
}
