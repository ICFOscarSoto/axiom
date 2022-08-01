<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Modules\IoT\Entity\IoTDevices;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStoresManagersVendingMachinesRepository")
 */
class ERPStoresManagersVendingMachines
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoresManagers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $serial;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastcheck;

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
     * @ORM\ManyToOne(targetEntity="App\Modules\IoT\Entity\IoTDevices")
     */
    private $iotdevice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManager(): ?ERPStoresManagers
    {
        return $this->manager;
    }

    public function setManager(?ERPStoresManagers $manager): self
    {
        $this->manager = $manager;

        return $this;
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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getLastcheck(): ?\DateTimeInterface
    {
        return $this->lastcheck;
    }

    public function setLastcheck(?\DateTimeInterface $lastcheck): self
    {
        $this->lastcheck = $lastcheck;

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

    public function getIotdevice(): ?\App\Modules\IoT\Entity\IoTDevices
    {
        return $this->iotdevice;
    }

    public function setIotdevice(?\App\Modules\IoT\Entity\IoTDevices $iotdevice): self
    {
        $this->iotdevice = $iotdevice;

        return $this;
    }

    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){
      $em = $doctrine->getManager();
      $iotDevices=$doctrine->getRepository(IoTDevices::class);
      if(!$this->iotdevice){
        //Creamos el dispositivo IoT asociado para la recepcion de datos de telemetría
        $iotdevice = new IoTDevices();
        $iotdevice->setCompany($this->manager->getCompany());
        $iotdevice->setName($this->manager->getName().'-'.$this->name);
        $token = openssl_random_pseudo_bytes(200);
        $token = bin2hex($token);
        $token .= md5(uniqid(time(), true));
        $iotdevice->setToken($token);
        $iotdevice->setDateadd(new \DateTime());
        $iotdevice->setDateupd(new \DateTime());
        $iotdevice->setActive(1);
        $iotdevice->setDeleted(0);
        $doctrine->getManager()->persist($iotdevice);
        $doctrine->getManager()->flush();
        $this->iotdevice=$iotdevice;
        $doctrine->getManager()->persist($this);
        $doctrine->getManager()->flush();
      }
    }
}
