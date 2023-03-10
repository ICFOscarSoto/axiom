<?php

namespace App\Modules\ERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleUsers;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\ERP\Entity\ERPSalesTicketsStates;
use \App\Modules\ERP\Entity\ERPSalesTicketsHistory;
use \App\Modules\ERP\Entity\ERPCustomers;
use \App\Modules\ERP\Entity\ERPSalesOrders;
use \App\Modules\HR\Entity\HRDepartments;
use \App\Modules\ERP\Entity\ERPSalesTicketsReasons;
use \App\Modules\ERP\Entity\ERPStoreTickets;
use \App\Modules\ERP\Entity\ERPStores;

/**
 * @ORM\Entity(repositoryClass="App\Modules\ERP\Repository\ERPSalesTicketsRepository")
 */
class ERPSalesTickets
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
    private $agent;

    /**
     * @ORM\Column(type="text")
     */
    private $observations;

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
    private $active=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSalesTicketsStates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $salesticketstate;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPCustomers")
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $customername;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSalesOrders")
     */
    private $salesorder;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salesordernumber;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $externalsalesordernumber;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\HR\Entity\HRDepartments")
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleUsers")
     */
   private $author;

    /**
    * @ORM\ManyToOne(targetEntity="\App\Modules\ERP\Entity\ERPSalesTicketsReasons")
    */
   private $reason;

   /**
    * @ORM\Column(type="datetime", nullable=true)
    */
   private $datelastnotify;

   /**
    * @ORM\OneToOne(targetEntity="\App\Modules\ERP\Entity\ERPStoreTickets", cascade={"persist", "remove"})
    */
   private $storeticket;

   /**
    * @ORM\Column(type="json_array", nullable=true)
    */
   private $products;

   /**
    * @ORM\Column(type="json_array", nullable=true)
    */
   private $stores;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(string $observations): self
    {
        $this->observations = $observations;

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

    public function getCompany(): ?GlobaleCompanies
    {
        return $this->company;
    }

    public function setCompany(?GlobaleCompanies $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getSalesticketstate(): ?ERPSalesTicketsStates
    {
        return $this->salesticketstate;
    }

    public function setSalesticketstate(?ERPSalesTicketsStates $salesticketstate): self
    {
        $this->salesticketstate = $salesticketstate;

        return $this;
    }

    public function getCustomer(): ?ERPCustomers
    {
        return $this->customer;
    }

    public function setCustomer(?ERPCustomers $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getCustomername(): ?string
    {
        return $this->customername;
    }

    public function setCustomername(?string $customername): self
    {
        $this->customername = $customername;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSalesorder(): ?ERPSalesOrders
    {
        return $this->salesorder;
    }

    public function setSalesorder(?ERPSalesOrders $salesorder): self
    {
        $this->salesorder = $salesorder;

        return $this;
    }

    public function getSalesordernumber(): ?string
    {
        return $this->salesordernumber;
    }

    public function setSalesordernumber(string $salesordernumber): self
    {
        $this->salesordernumber = $salesordernumber;

        return $this;
    }

    public function postProccess($kernel, $doctrine, $user, $params, $oldobj){

       $em = $doctrine->getManager();
       $history_obj=new ERPSalesTicdketsHistory();
       $history_obj->setAgent($this->agent);
       $history_obj->setSalesTicket($this);
       $history_obj->setObservations($this->observations);
       $history_obj->setActive(1);
       $history_obj->setDeleted(0);
       $history_obj->setDateupd(new \DateTime());
       $history_obj->setDateadd(new \DateTime());
       $em->persist($history_obj);
       $em->flush();

     }

    public function getExternalsalesordernumber(): ?string
    {
        return $this->externalsalesordernumber;
    }

    public function setExternalsalesordernumber(?string $externalsalesordernumber): self
    {
        $this->externalsalesordernumber = $externalsalesordernumber;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDepartment(): ?HRDepartments
    {
        return $this->department;
    }

    public function setDepartment(?HRDepartments $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getAuthor(): ?GlobaleUsers
    {
        return $this->author;
    }

    public function setAuthor(?GlobaleUsers $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getReason(): ?ERPSalesTicketsReasons
    {
        return $this->reason;
    }

    public function setReason(?ERPSalesTicketsReasons $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getDatelastnotify(): ?\DateTimeInterface
    {
        return $this->datelastnotify;
    }

    public function setDatelastnotify(?\DateTimeInterface $datelastnotify): self
    {
        $this->datelastnotify = $datelastnotify;

        return $this;
    }

    public function getStoreticket(): ?ERPStoreTickets
    {
        return $this->storeticket;
    }

    public function setStoreticket(?ERPStoreTickets $storeticket): self
    {
        $this->storeticket = $storeticket;

        return $this;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts($products): self
    {
        $this->products = $products;

        return $this;
    }

    public function getStores()
    {
        return $this->stores;
    }

    public function setStores($stores): self
    {
        $this->stores = $stores;

        return $this;
    }

}
