<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPSalesBudgets;
use App\Modules\ERP\Entity\ERPSalesBudgetsLines;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class AXIOMGetBudgets extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:createbudgets')
            ->setDescription('Sync navision principal entities')
            ->addArgument('entity', InputArgument::REQUIRED, '¿Entidad que sincronizar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    $entity = $input->getArgument('entity');

    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $this->company=$repositoryCompanies->find(2);

    $output->writeln('');
    $output->writeln('Comenzando sincronizacion Navision');
    $output->writeln('==================================');
    switch($entity){
      case 'budgets': $this->createBudgets($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }


  public function clean($string) {
     $string=str_replace(' ', ' ', $string); //Replace non white space char for white space
     $string=preg_replace('/[^A-Za-zÁ-Úá-ú0-9\s,.!?+\\-"\'()]/', '', $string); // Removes special chars.
     $string=trim($string);
     return $string;
  }



  public function createBudgets(InputInterface $input, OutputInterface $output){
    //------   Create Lock Mutex    ------
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-createBudgets.lock', 'c');
    } else {
        $fp = fopen('/tmp/axiom-navisionGetProducts-createBudgets.lock', 'c');
    }

    if (!flock($fp, LOCK_EX | LOCK_NB)) {
      $output->writeln('* Fallo al iniciar la creación de presupuestos en Navision: El proceso ya esta en ejecución.');
      exit;
    }

    //------   Critical Section START   ------
    $repositorySalesBudgets=$this->doctrine->getRepository(ERPSalesBudgets::class);
    $repositorySalesBudgetsLines=$this->doctrine->getRepository(ERPSalesBudgetsLines::class);

    //$orders=$repositoryPurchasesOrders->findAll();
    $budgets_id=$repositorySalesBudgets->findNews();

    foreach($budgets_id as $budget_id){
      $budget=$repositorySalesBudgets->findOneBy(["id"=>$budget_id]);
      if($budget->getCode()!="20OFV11510") continue;

      $output->writeln("Insertando el presupuesto: ".$budget->getCode());

      if ($budget->getAuthor()->getName()=="Administrador") $author=null;
      else $author=$budget->getAuthor()->getEmail();
      if ($budget->getAgent()->getName()=="Administrador") $agent=null;
      else $agent=$budget->getAuthor()->getEmail();
      if ($budget->getWebsale()) $web=1;
      else $web=0;

      $estadooferta=2;
      $devolucion=0;

      $budgetJson=["No."=>$this->clean($budget->getCode()),
      "Bill-to Customer No."=>$this->clean($budget->getCustomercode()),
      "Bill-to Name"=>substr($this->clean($budget->getCustomername()),0,50),
      "Bill-to Name 2"=>substr($this->clean($budget->getCustomername()),50,50),
      "Bill-to Address"=>substr($this->clean($budget->getCustomeraddress()),0,50),
      "Bill-to Address 2"=>substr($this->clean($budget->getCustomeraddress()),50,50),
      "Bill-to City"=>$this->clean($budget->getCustomercity()),
      "Ship-to Name"=>substr($this->clean($budget->getShiptoname()),0,50),
      "Ship-to Name 2"=>substr($this->clean($budget->getShiptoname()),50,50),
      "Ship-to Address"=>substr($this->clean($budget->getShiptoaddress()),0,50),
      "Ship-to Address 2"=>substr($this->clean($budget->getShiptoaddress()),50,50),
      "Shipment Date"=>$budget->getShipmentdate(),
      "VAT Registration No."=>$this->clean($budget->getVat()),
      "Ship-to City"=>$this->clean($budget->getShiptocity()),
      "Bill-to Post Code"=>$budget->getCustomerpostcode(),
      "Bill-to County"=>$this->clean($budget->getCustomerstate()),
      "Ship-to Post Code"=>$budget->getShiptopostcode(),
      "Ship-to County"=>$this->clean($budget->getShiptostate()),
      "Document Date"=>$budget->getDate(),
      "Payment Method Code"=>$budget->getPaymentmethod()?$budget->getPaymentmethod()->getPaymentcode():'',
      "Status"=>$budget->getStatus(),
      "No oferta relacionada"=>$budget->getSalesbudget()?$budget->getSalesbudget()->getCode():'',
      "Fecha Limite Validez Oferta"=>$budget->getDateofferend(),
      "Pedido WEB"=>$web,
      "Order Date"=>$budget->getDateadd(),
      "Es devolucion"=>$devolucion,
      "Assigned User ID"=>$author,
      "Agent"=>$agent,
      "No. Series"=>$numeroseries,
      "Proforma invoice"=>$proforma,
      "Estado Oferta"=>$estadooferta
      ];

      $budgetLinesArray=[];

      $budgetlines=$repositorySalesBudgetsLines->findBy(["salesbudget"=>$budget]);

      foreach($budgetlines as $budgetline){
      //  $productrepository=$doctrine->getRepository(ERPProducts::class);
      //$product=$productrepository->getOneBy(["id"=>$orderline->getProduct()->getId()]);

       $output->writeln("   > ".$budgetline->getCode());

        $quantity=$budgetline->getQuantity();
        if($quantity==0) $quantity=1;
        $unitprice=$budgetline->getUnitprice();
        $total=$budgetline->getTotal();
        $dto=$budgetline->getDtoperc();
        $linenum=$budgetline->getLinenum()*10000;
        $line[]=[
          "No."=>$budgetline->getCode(),
          "Document No."=>$budgetline->getCode(),
          "Description"=>substr($this->clean($budgetline->getName()),0,50),
          "Description 2"=>substr($this->clean($budgetline->getName()),50,50),
          "Quantity"=>$quantity,
          "Discounttotal"=>$budgetline->getDtounit(),
          "Discountperc"=>$budgetline->getDtoperc(),
          "Unit Price"=>$unitprice,
          "Unit price UM precio"=>$unitprice,
          "Unit Cost"=>round($budgetline->getCost()/$quantity,2),
          "VAT Base Amount"=>round($total/1.21,2),
          "Line Amount"=>round($total/1.21,2),
          "Importe pendiente base"=>round($total/1.21,2),
          "Importe pendiente base (DL)"=>round($total/1.21,2),
          "Amount"=>round($total/1.21,2),
          "Amount Including VAT"=>$total,
        ];

        $budgetLinesArray=$line;

      }

      $budgetJson["lines"]=$budgetLinesArray;

      dump(json_encode($orderJson));
    //  $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createBudgets.php?json='.urlencode(json_encode($budgetJson)));

    }

    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);

  }


}
?>
