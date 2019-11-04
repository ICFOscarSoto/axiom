<?php

namespace App\Modules\AERP\Entity;

use Doctrine\ORM\Mapping as ORM;
use \App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Modules\Globale\Entity\GlobaleTaxes;
use \App\Modules\AERP\Entity\AERPPaymentMethods;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Modules\AERP\Entity\AERPConfigurationRepository")
 */
class AERPConfiguration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleCompanies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $margincontrol=false;

    /**
     * @ORM\Column(type="float")
     */
    private $margin=0;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\Globale\Entity\GlobaleTaxes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $defaulttax;

    /**
     * @ORM\Column(type="boolean")
     */
    private $irpf=false;

    /**
     * @ORM\Column(type="float")
     */
    private $defaultirpf=false;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Modules\AERP\Entity\AERPPaymentMethods")
     */
    private $defaultpaymentmethod;

    /**
     * @ORM\Column(type="integer")
     */
    private $budgetexpiration=30;

    /**
     * @ORM\Column(type="smallint")
     */
    private $budgetexpirationtype=1;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateadd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateupd;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $bgcolor;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private $shadowcolor;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $lopd;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $register;

    public function __construct($kernel=null, $doctrine=null, $user=null, $company=null)
    {
      if($kernel && $doctrine && $user && $company){
       $classTaxes="\App\Modules\Globale\Entity\GlobaleTaxes";
       $repositoryTaxes=$doctrine->getRepository($classTaxes);
       $this->defaulttax=$repositoryTaxes->find(1);

       $classPaymentMethods="\App\Modules\AERP\Entity\AERPPaymentMethods";
       $repositoryPaymentMethods=$doctrine->getRepository($classTaxes);
       $paymentmethod=new $classPaymentMethods();
       $paymentmethod->setCompany($company);
       $paymentmethod->setName("CONTADO");
       $paymentmethod->setExpiration(0);
       $paymentmethod->setType(0);
       $paymentmethod->setDomiciled(0);
       $paymentmethod->setDateadd(new \Datetime());
       $paymentmethod->setDateupd(new \Datetime());
       $paymentmethod->setActive(1);
       $paymentmethod->setDeleted(0);
       $doctrine->getManager()->persist($paymentmethod);
       $doctrine->getManager()->flush();
       $this->defaultpaymentmethod=$paymentmethod;
     }

    }

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

    public function getMargincontrol(): ?bool
    {
        return $this->margincontrol;
    }

    public function setMargincontrol(bool $margincontrol): self
    {
        $this->margincontrol = $margincontrol;

        return $this;
    }

    public function getMargin(): ?float
    {
        return $this->margin;
    }

    public function setMargin(float $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    public function getDefaulttax(): ?GlobaleTaxes
    {
        return $this->defaulttax;
    }

    public function setDefaulttax(?GlobaleTaxes $defaulttax): self
    {
        $this->defaulttax = $defaulttax;

        return $this;
    }

    public function getIrpf(): ?bool
    {
        return $this->irpf;
    }

    public function setIrpf(bool $irpf): self
    {
        $this->irpf = $irpf;

        return $this;
    }

    public function getDefaultirpf(): ?float
    {
        return $this->defaultirpf;
    }

    public function setDefaultirpf(float $defaultirpf): self
    {
        $this->defaultirpf = $defaultirpf;

        return $this;
    }

    public function getDefaultpaymentmethod(): ?AERPPaymentMethods
    {
        return $this->defaultpaymentmethod;
    }

    public function setDefaultpaymentmethod(?AERPPaymentMethods $defaultpaymentmethod): self
    {
        $this->defaultpaymentmethod = $defaultpaymentmethod;

        return $this;
    }

    public function getBudgetexpiration(): ?int
    {
        return $this->budgetexpiration;
    }

    public function setBudgetexpiration(int $budgetexpiration): self
    {
        $this->budgetexpiration = $budgetexpiration;

        return $this;
    }

    public function getBudgetexpirationtype(): ?int
    {
        return $this->budgetexpirationtype;
    }

    public function setBudgetexpirationtype(int $budgetexpirationtype): self
    {
        $this->budgetexpirationtype = $budgetexpirationtype;

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

    public function getBgcolor(): ?string
    {
        return $this->bgcolor;
    }

    public function setBgcolor(string $bgcolor): self
    {
        $this->bgcolor = $bgcolor;

        return $this;
    }

    public function getShadowcolor(): ?string
    {
        return $this->shadowcolor;
    }

    public function setShadowcolor(string $shadowcolor): self
    {
        $this->shadowcolor = $shadowcolor;

        return $this;
    }

    public function getLopd(): ?string
    {
        return $this->lopd;
    }

    public function setLopd(?string $lopd): self
    {
        $this->lopd = $lopd;

        return $this;
    }

    public function getRegister(): ?string
    {
        return $this->register;
    }

    public function setRegister(?string $register): self
    {
        $this->register = $register;

        return $this;
    }
}
