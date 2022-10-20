<?php

namespace App\Modules\ERP\Entity;

use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use \App\Modules\ERP\Entity\ERPStocksHistory;
use \App\Modules\ERP\Entity\ERPStocks;
use \App\Modules\ERP\Entity\ERPStoreLocations;
use \App\Modules\ERP\Entity\ERPTypesMovements;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPStoresManagersOperationsRepository")
 */
class ERPStoresManagersOperations
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoresManagers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manager;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\Globale\Entity\GlobaleUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoresManagersConsumers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $consumer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStores")
     * @ORM\JoinColumn(nullable=true)
     */
    private $store;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

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
     * @ORM\ManyToOne(targetEntity="App\Modules\ERP\Entity\ERPStoresManagersVendingMachines")
     */
    private $vendingmachine;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getAgent(): ?GlobaleUsers
    {
        return $this->agent;
    }

    public function setAgent(?GlobaleUsers $agent): self
    {
        $this->agent = $agent;

        return $this;
    }

    public function getConsumer(): ?ERPStoresManagersConsumers
    {
        return $this->consumer;
    }

    public function setConsumer(?ERPStoresManagersConsumers $consumer): self
    {
        $this->consumer = $consumer;

        return $this;
    }

    public function getStore(): ?ERPStores
    {
        return $this->store;
    }

    public function setStore(?ERPStores $store): self
    {
        $this->store = $store;

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

    public function getManager(): ?ERPStoresManagers
    {
        return $this->manager;
    }

    public function setManager(?ERPStoresManagers $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getVendingmachine(): ?ERPStoresManagersVendingMachines
    {
        return $this->vendingmachine;
    }

    public function setVendingmachine(?ERPStoresManagersVendingMachines $vendingmachine): self
    {
        $this->vendingmachine = $vendingmachine;

        return $this;
    }

    public function delete($doctrine){
      $em = $doctrine->getManager();
      $userRepository=$doctrine->getRepository(GlobaleUsers::class);
      $user=$userRepository->findOneBy(["email"=>"paco.cano@ferreteriacampollano.com"]);
      $linesRepository=$doctrine->getRepository(ERPStoresManagersOperationsLines::class);
      $stocksRepository=$doctrine->getRepository(ERPStocks::class);
      $storeLocationsRepository=$doctrine->getRepository(ERPStoreLocations::class);
      $typesRepository=$doctrine->getRepository(ERPTypesMovements::class);
      $channelsRepository=$doctrine->getRepository(ERPStoresManagersVendingMachinesChannels::class);
      $lines=$linesRepository->findBy(["operation"=>$this]);
      foreach ($lines as $line){
        $line->setActive(0);
        $line->setDeleted(1);
        $line->setDateupd(new \Datetime());
        if ($this->getStore()!=null) {
          $stockHistory=new ERPStocksHistory();
          $stockHistory->setUser($user);
          $stockHistory->setCompany($user->getCompany());
          $stockHistory->setQuantity($line->getQuantity());
          $location=$storeLocationsRepository->findOneBy(["store"=>$this->getStore(), "company"=>$user->getCompany(), "active"=>1,"deleted"=>0]);
          $stock=$stocksRepository->findOneBy(["productvariant"=>$line->getProductvariant(), "company"=>$user->getCompany(), "storelocation"=>$location, "active"=>1, "deleted"=>0]);
          $stockHistory->setLocation($location);
          $stockHistory->setPreviousqty($stock->getQuantity());
          $stockHistory->setProductvariant($line->getProductvariant());
          $stockHistory->setNewqty($stock->getQuantity()+$line->getQuantity());
          $stockHistory->setNumOperation('DO-'.$this->getId());
          $type=$typesRepository->findOneBy(["name"=>"Ajuste de inventario"]);
          $stockHistory->setType($type);
          $stockHistory->setDateadd(new \Datetime());
          $stockHistory->setDateupd(new \Datetime());
          $stockHistory->setActive(true);
          $stockHistory->setDeleted(false);
          $em->persist($stockHistory);
        }
        $em->persist($line);
        $em->flush();
      }
    }

}
