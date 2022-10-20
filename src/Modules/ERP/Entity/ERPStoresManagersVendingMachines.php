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

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoreLocations")
     */
    private $storelocation;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $replenishmentnotifytype=0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $replenishmentnotifyaddress;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $simoperator;
    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $simnumber;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $simpin;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $vpnuser;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $vpnpassword;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $vpnip;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $modembrand;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $modemmodel;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $modemserial;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $localuser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alertnotifyaddress;

    /**
     * @ORM\Column(type="boolean")
     */
    private $connectionlostnotified;

    /**
    * @ORM\Column(type="boolean")
    */
    private $opencontrollerdoornotified;



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
        $iotdevice->setToken(substr($token,0,255));
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

    public function getStorelocation(): ?ERPStoreLocations
    {
        return $this->storelocation;
    }

    public function setStorelocation(?ERPStoreLocations $storelocation): self
    {
        $this->storelocation = $storelocation;

        return $this;
    }

    public function getReplenishmentnotifytype(): ?int
    {
        return $this->replenishmentnotifytype;
    }

    public function setReplenishmentnotifytype(?int $replenishmentnotifytype): self
    {
        $this->replenishmentnotifytype = $replenishmentnotifytype;

        return $this;
    }

    public function getReplenishmentnotifyaddress(): ?string
    {
        return $this->replenishmentnotifyaddress;
    }

    public function setReplenishmentnotifyaddress(?string $replenishmentnotifyaddress): self
    {
        $this->replenishmentnotifyaddress = $replenishmentnotifyaddress;

        return $this;
    }
    public function getSimoperator(): ?string
    {
        return $this->simoperator;
    }

    public function setSimoperator(?string $simoperator): self
    {
        $this->simoperator = $simoperator;

        return $this;
    }

    public function getSimnumber(): ?string
    {
        return $this->simnumber;
    }

    public function setSimnumber(?string $simnumber): self
    {
        $this->simnumber = $simnumber;

        return $this;
    }

    public function getSimpin(): ?string
    {
        return $this->simpin;
    }

    public function setSimpin(?string $simpin): self
    {
        $this->simpin = $simpin;

        return $this;
    }

    public function getVpnuser(): ?string
    {
        return $this->vpnuser;
    }

    public function setVpnuser(?string $vpnuser): self
    {
        $this->vpnuser = $vpnuser;

        return $this;
    }

    public function getVpnpassword(): ?string
    {
        return $this->vpnpassword;
    }

    public function setVpnpassword(?string $vpnpassword): self
    {
        $this->vpnpassword = $vpnpassword;

        return $this;
    }

    public function getVpnip(): ?string
    {
        return $this->vpnip;
    }

    public function setVpnip(?string $vpnip): self
    {
        $this->vpnip = $vpnip;

        return $this;
    }

    public function getModembrand(): ?string
    {
        return $this->modembrand;
    }

    public function setModembrand(?string $modembrand): self
    {
        $this->modembrand = $modembrand;

        return $this;
    }

    public function getModemmodel(): ?string
    {
        return $this->modemmodel;
    }

    public function setModemmodel(?string $modemmodel): self
    {
        $this->modemmodel = $modemmodel;

        return $this;
    }

    public function getModemserial(): ?string
    {
        return $this->modemserial;
    }

    public function setModemserial(?string $modemserial): self
    {
        $this->modemserial = $modemserial;

        return $this;
    }

    public function getLocaluser(): ?string
    {
        return $this->localuser;
    }

    public function setLocaluser(?string $localuser): self
    {
        $this->localuser = $localuser;

        return $this;
    }

    public function getAlertnotifyaddress(): ?string
    {
        return $this->alertnotifyaddress;
    }

    public function setAlertnotifyaddress(?string $alertnotifyaddress): self
    {
        $this->alertnotifyaddress = $alertnotifyaddress;

        return $this;
    }

    public function getConnectionlostnotified(): ?bool
    {
        return $this->connectionlostnotified;
    }

    public function setConnectionlostnotified(bool $connectionlostnotified): self
    {
        $this->connectionlostnotified = $connectionlostnotified;

        return $this;
    }

    public function getOpencontrollerdoornotified(): ?bool
    {
        return $this->opencontrollerdoornotified;
    }

    public function setOpencontrollerdoornotified(bool $opencontrollerdoornotified): self
    {
        $this->opencontrollerdoornotified = $opencontrollerdoornotified;

        return $this;
    }

}
