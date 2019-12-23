<?php

namespace App\Modules\Navision\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\Navision\Entity\NavisionSyncRepository")
 */
class NavisionSync
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $entity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastsync;

    /**
     * @ORM\Column(type="bigint")
     */
    private $maxtimestamp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getLastsync(): ?\DateTimeInterface
    {
        return $this->lastsync;
    }

    public function setLastsync(\DateTimeInterface $lastsync): self
    {
        $this->lastsync = $lastsync;

        return $this;
    }

    public function getMaxtimestamp(): ?int
    {
        return $this->maxtimestamp;
    }

    public function setMaxtimestamp(int $maxtimestamp): self
    {
        $this->maxtimestamp = $maxtimestamp;

        return $this;
    }
}
