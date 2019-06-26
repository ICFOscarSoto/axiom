<?php

namespace App\Modules\Globale\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Utils\GlobaleClockDevicesUtils;

/**
 * @ORM\Entity(repositoryClass="App\Modules\Globale\Repository\GlobaleClockDevicesRepository")
 */
class GlobaleClockDevices
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $identifier;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $serialnumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $firmware;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $protocol;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $expirestime;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     */
    private $company;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $createdtime;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $lasttime;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $islogin;

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
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $usefunction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getSerialnumber(): ?string
    {
        return $this->serialnumber;
    }

    public function setSerialnumber(?string $serialnumber): self
    {
        $this->serialnumber = $serialnumber;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getFirmware(): ?string
    {
        return $this->firmware;
    }

    public function setFirmware(?string $firmware): self
    {
        $this->firmware = $firmware;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getToken(): ?int
    {
        return $this->token;
    }

    public function setToken(?int $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpirestime(): ?int
    {
        return $this->expirestime;
    }

    public function setExpirestime(?int $expirestime): self
    {
        $this->expirestime = $expirestime;

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

    public function getCreatedtime(): ?int
    {
        return $this->createdtime;
    }

    public function setCreatedtime(?int $createdtime): self
    {
        $this->createdtime = $createdtime;

        return $this;
    }

    public function getLasttime(): ?int
    {
        return $this->lasttime;
    }

    public function setLasttime(?int $lasttime): self
    {
        $this->lasttime = $lasttime;

        return $this;
    }

    public function getIslogin(): ?bool
    {
        return $this->islogin;
    }

    public function setIsLogin(?bool $islogin): self
    {
        $this->islogin = $islogin;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUsefunction(): ?bool
    {
        return $this->usefunction;
    }

    public function setUsefunction(bool $usefunction): self
    {
        $this->usefunction = $usefunction;

        return $this;
    }

    public function postProccess($kernel, $doctrine, $user){
      if($this->getActive() && !$this->getDeleted()){
        $utils = new GlobaleClockDevicesUtils();
        $params=["id"=>$this->getIdentifier()];
        $utils->setdatetime($params);
      }
    }
}
