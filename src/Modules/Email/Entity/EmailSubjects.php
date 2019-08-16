<?php
namespace App\Modules\Email\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Email\Repository\EmailSubjectsRepository")
 */
class EmailSubjects
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fromEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $toEmail;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $messageId;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $size;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $uid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $msgno;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $recent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $flagged;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $answered;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $seen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $draft;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Email\Entity\EmailFolders", inversedBy="emailSubjects")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $folder;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $attachments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getToEmail(): ?string
    {
        return $this->toEmail;
    }

    public function setToEmail(?string $toEmail): self
    {
        $this->toEmail = $toEmail;

        return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(?float $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(?int $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getMsgno(): ?int
    {
        return $this->msgno;
    }

    public function setMsgno(?int $msgno): self
    {
        $this->msgno = $msgno;

        return $this;
    }

    public function getRecent(): ?bool
    {
        return $this->recent;
    }

    public function setRecent(?bool $recent): self
    {
        $this->recent = $recent;

        return $this;
    }

    public function getFlagged(): ?bool
    {
        return $this->flagged;
    }

    public function setFlagged(?bool $flagged): self
    {
        $this->flagged = $flagged;

        return $this;
    }

    public function getAnswered(): ?bool
    {
        return $this->answered;
    }

    public function setAnswered(?bool $answered): self
    {
        $this->answered = $answered;

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

    public function getSeen(): ?bool
    {
        return $this->seen;
    }

    public function setSeen(?bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }

    public function getDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(?bool $draft): self
    {
        $this->draft = $draft;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFolder(): ?EmailFolders
    {
        return $this->folder;
    }

    public function setFolder(?EmailFolders $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getAttachments(): ?int
    {
        return $this->attachments;
    }

    public function setAttachments(?int $attachments): self
    {
        $this->attachments = $attachments;

        return $this;
    }
}
