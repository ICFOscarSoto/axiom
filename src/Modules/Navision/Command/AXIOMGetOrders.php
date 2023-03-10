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
use App\Modules\ERP\Entity\ERPPurchasesOrders;
use App\Modules\ERP\Entity\ERPPurchasesOrdersLines;
use App\Modules\ERP\Entity\ERPSalesOrders;
use App\Modules\ERP\Entity\ERPSalesOrdersLines;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class AXIOMGetOrders extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:createorders')
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
      case 'purchasesOrders': $this->createOrders($input, $output);
      break;
      case 'salesOrders': $this->createSales($input, $output);
      break;
      case 'checkPurchaseOrders': $this->checkPurchaseOrders($input, $output);
      break;
      case 'checkSaleOrders': $this->checkSaleOrders($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }


  public function createOrders(InputInterface $input, OutputInterface $output){
    //------   Create Lock Mutex    ------
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-createOrders.lock', 'c');
    } else {
        $fp = fopen('/tmp/axiom-navisionGetProducts-createOrders.lock', 'c');
    }

    if (!flock($fp, LOCK_EX | LOCK_NB)) {
      $output->writeln('* Fallo al iniciar la creación de pedidos en Navision: El proceso ya esta en ejecución.');
      exit;
    }

    //------   Critical Section START   ------
    $repositoryPurchasesOrders=$this->doctrine->getRepository(ERPPurchasesOrders::class);
    $repositoryPurchasesOrdersLines=$this->doctrine->getRepository(ERPPurchasesOrdersLines::class);

    //$orders=$repositoryPurchasesOrders->findAll();
    $orders_id=$repositoryPurchasesOrders->getPurchasesOrdersByDate();

    foreach($orders_id as $order_id){
      $order=$repositoryPurchasesOrders->findOneBy(["id"=>$order_id]);
     //if($order->getCode()!="20PC09117") continue;
      if (strncmp($order->getCode(), "20PC", 4) === 0) $devolucion=0;
      else $devolucion=1;

      //if($devolucion==0 OR $order->getCode()=="20DEVC00644") continue;

      $output->writeln("Insertando el pedido: ".$order->getCode());

      if ($order->getAuthor()->getName()=="Administrador") $author=null;
      else $author=$order->getAuthor()->getEmail();
      if ($order->getAgent()->getName()=="Administrador") $agent=null;
      else $agent=$order->getAuthor()->getEmail();


    //  $num=(int)substr($order->getCode(),5);
    //  if($order->getCode()!="20PC08951") continue;

      $orderJson=["No."=>$order->getCode(),
      "Buy-from Vendor No."=>$order->getSuppliercode(),
      "Assigned User ID"=>$author,
      "Purchaser Code"=>$order->getAgent()->getEmail(),
      "Buy-from Vendor Name"=>substr($this->clean($order->getSuppliername()),0,50),
      "Buy-from Vendor Name 2"=>substr($this->clean($order->getSuppliername()),50,50),
      "Buy-from Address"=>substr($this->clean($order->getSupplieraddress()),0,50),
      "Buy-from Address 2"=>substr($this->clean($order->getSupplieraddress()),50,50),
      "Buy-from Post Code"=>$order->getSupplierpostcode(),
      "Buy-from City"=>$this->clean($order->getSuppliercity()),
      "Buy-from County"=>$this->clean($order->getSupplierstate()),
      "No oferta relacionada"=>$order->getPurchasesbudget()?$order->getPurchasesbudget()->getCode():'',
      "Ship-to Post Code"=>$order->getSupplierpostcode(),
      "Status"=>$order->getStatus(),
      "Payment Method Code"=>$order->getSupplier()?$order->getSupplier()->getPaymentmethod()->getPaymentcode():'',
      "VAT Registration No."=>$order->getVat(),
      "Document Date" => $order->getDate(),
      "Fecha 1.lanzamiento" => $order->getDateofferend(),
      "Es Devolucion"=>$devolucion,
      "Order Date"=>$order->getDateadd(),
      "Agent"=>$agent    ];

    //  $output->writeln(json_encode($orderJson));


      $orderlines=$repositoryPurchasesOrdersLines->findBy(["purchasesorder"=>$order]);

      foreach($orderlines as $orderline){
      //  $productrepository=$doctrine->getRepository(ERPProducts::class);
      //$product=$productrepository->getOneBy(["id"=>$orderline->getProduct()->getId()]);

       $output->writeln("   > ".$orderline->getCode());

        $quantity=$orderline->getQuantity();
        if($quantity==0) $quantity=1;
        $unitprice=$orderline->getUnitprice();
        $total=$orderline->getTotal();
        $dto=$orderline->getDtoperc();
        $linenum=$orderline->getLinenum()*10000;
        $line=[
          "No."=>$orderline->getCode(),
          "Document No."=>$order->getCode(),
        /*  "Cross-Reference No."=>,*/
          "Description"=>substr($this->clean($orderline->getName()),0,50),
          "Description 2"=>substr($this->clean($orderline->getName()),50,50),
          "Quantity"=>$quantity,
          "Outstanding Quantity"=>$quantity,
          "Line Discount %"=>$dto,
          "Line Discount Amount"=>$orderline->getDtounit(),
          "Amount"=>round($total/1.21,2),
          "Amount including VAT"=>$total,
          "type"=>2,
          "Line No."=>$linenum,
          /*"EC %" vat,*/
          "VAT %"=>$orderline->getTaxperc(),
          "Direct Unit Cost"=>$unitprice,
          "Unit price (LCY)"=>$unitprice,
          "Unit Cost (LCY)"=>round(($total/1.21)/$quantity,2),
          "Unit Cost"=>round(($total/1.21)/$quantity,2),
          "Line Discount Amount"=>($unitprice*$quantity)*($dto/100),
          "Coste unit. directo UM precio"=>$unitprice,
          "Unit Cost UM Precio"=>($unitprice)*($dto/100),
          "VAT Base Amount"=>round($total/1.21,2),
          "Line Amount"=>round($total/1.21,2),
          "Importe pendiente base"=>round($total/1.21,2),
          "Importe pendiente base (DL)"=>round($total/1.21,2)
        ];

        $orderJson["lines"][]=$line;

      }



      //   dump(json_encode($orderJson));
    //   $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createPurchasesOrders.php?json='.urlencode(json_encode($orderJson)));

    $postdata = http_build_query(
        array(
            'json' => json_encode($orderJson)
        )
    );
    $opts = array('http' =>
      array(
          'method'  => 'POST',
          'header'  => 'Content-Type: application/x-www-form-urlencoded',
          'content' => $postdata
      )
    );
    $context  = stream_context_create($opts);
    $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createPurchasesOrders.php', false, $context);



    }

    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);

  }

  public function clean($string) {
     $string=str_replace(' ', ' ', $string); //Replace non white space char for white space
     $string=preg_replace('/[^A-Za-zÁ-Úá-ú0-9\s,.!?+\\-"\'()]/', '', $string); // Removes special chars.
     $string=trim($string);
     return $string;
  }



  public function createSales(InputInterface $input, OutputInterface $output){
        //------   Create Lock Mutex    ------
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-createSales.lock', 'c');
        } else {
            $fp = fopen('/tmp/axiom-navisionGetProducts-createSales.lock', 'c');
        }

        if (!flock($fp, LOCK_EX | LOCK_NB)) {
          $output->writeln('* Fallo al iniciar la creación de pedidos en Navision: El proceso ya esta en ejecución.');
          exit;
        }

        //------   Critical Section START   ------
        $repositorySalesOrders=$this->doctrine->getRepository(ERPSalesOrders::class);
        $repositorySalesOrdersLines=$this->doctrine->getRepository(ERPSalesOrdersLines::class);



        $orders_id=$repositorySalesOrders->findNews();
        //$orders=$repositorySalesOrders->findBy(["code"=>"20PV40303"]);
        foreach($orders_id as $order_id){
          $order=$repositorySalesOrders->findOneBy(["id"=>$order_id]);
          //foreach($orders as $order){
          if (strncmp($order->getCode(), "20PV", 4) === 0) $devolucion=0;
          else $devolucion=1;
          $output->writeln("Insertando el pedido: ".$order->getCode());
          if ($order->getAuthor()->getName()=="Administrador") $author=null;
          else $author=$order->getAuthor()->getEmail();
          if ($order->getAgent()->getName()=="Administrador") $agent=null;
          else $agent=$order->getAuthor()->getEmail();
          if ($order->getWebsale()) $web=1;
          else $web=0;
          $orderJson=["No."=>$this->clean($order->getCode()),
          "Bill-to Customer No."=>$this->clean($order->getCustomercode()),
          "Bill-to Name"=>substr($this->clean($order->getCustomername()),0,50),
          "Bill-to Name 2"=>substr($this->clean($order->getCustomername()),50,50),
          "Bill-to Address"=>substr($this->clean($order->getCustomeraddress()),0,50),
          "Bill-to Address 2"=>substr($this->clean($order->getCustomeraddress()),50,50),
          "Bill-to City"=>$this->clean($order->getCustomercity()),
          "Ship-to Name"=>substr($this->clean($order->getShiptoname()),0,50),
          "Ship-to Name 2"=>substr($this->clean($order->getShiptoname()),50,50),
          "Ship-to Address"=>substr($this->clean($order->getShiptoaddress()),0,50),
          "Ship-to Address 2"=>substr($this->clean($order->getShiptoaddress()),50,50),
          "Shipment Date"=>$order->getShipmentdate(),
          "VAT Registration No."=>$this->clean($order->getVat()),
          "Ship-to City"=>$this->clean($order->getShiptocity()),
          "Bill-to Post Code"=>$order->getCustomerpostcode(),
          "Bill-to County"=>$this->clean($order->getCustomerstate()),
          "Ship-to Post Code"=>$order->getShiptopostcode(),
          "Ship-to County"=>$this->clean($order->getShiptostate()),
          "Document Date"=>$order->getDate(),
          "Payment Method Code"=>$order->getPaymentmethod()?$order->getPaymentmethod()->getPaymentcode():'',
          "Status"=>$order->getStatus(),
          "No oferta relacionada"=>$order->getSalesbudget()?$order->getSalesbudget()->getCode():'',
          "Fecha Limite Validez Oferta"=>$order->getDateofferend(),
          "Pedido WEB"=>$web,
          "Order Date"=>$order->getDateadd(),
          "Es devolucion"=>$devolucion,
          "Assigned User ID"=>$author,
          "Agent"=>$agent
        ];

        $orderlines=$repositorySalesOrdersLines->findBy(["salesorder"=>$order]);

        foreach($orderlines as $orderline){

         $output->writeln("   > ".$orderline->getCode());

          $quantity=$orderline->getQuantity();
          if($quantity==0) $quantity=1;
          $unitprice=$orderline->getUnitprice();
          $total=$orderline->getTotal();
          $dto=$orderline->getDtoperc();
          $linenum=$orderline->getLinenum()*10000;
          $line=[
            "No."=>$orderline->getCode(),
            "Document No."=>$order->getCode(),
            "Line No."=>$linenum,
            "Description"=>substr($this->clean($orderline->getName()),0,50),
            "Description 2"=>substr($this->clean($orderline->getName()),50,50),
            "Quantity"=>$quantity,
            "Discounttotal"=>$orderline->getDtounit(),
            "Discountperc"=>$orderline->getDtoperc(),
            "Unit Price"=>$unitprice,
            "Unit price UM precio"=>$unitprice,
            "Unit Cost"=>round($orderline->getCost()/$quantity,2),
            "VAT Base Amount"=>round($total/1.21,2),
            "Line Amount"=>round($total/1.21,2),
            "Importe pendiente base"=>round($total/1.21,2),
            "Importe pendiente base (DL)"=>round($total/1.21,2),
            "Amount"=>round($total/1.21,2),
            "Amount Including VAT"=>$total
          ];
          //$orderLinesArray=$line;
          $orderJson["lines"][]=$line;
        }

    //  $orderJson["lines"]=$orderLinesArray;

        $postdata = http_build_query(
            array(
                'json' => json_encode($orderJson)
            )
        );
        $opts = array('http' =>
          array(
              'method'  => 'POST',
              'header'  => 'Content-Type: application/x-www-form-urlencoded',
              'content' => $postdata
          )
        );
        $context  = stream_context_create($opts);
        $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createSalesOrders.php', false, $context);

      }
    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    //------

    fclose($fp);
    }

    public function checkPurchaseOrders(InputInterface $input, OutputInterface $output)
    {

      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-checkPurchaseOrders.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionGetProducts-checkPurchase.lock', 'c');
      }

      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la creación de pedidos en Navision: El proceso ya esta en ejecución.');
        exit;
      }

      //------   Critical Section START   ------
      $repositoryPurchasesOrders=$this->doctrine->getRepository(ERPPurchasesOrders::class);
      $repositoryPurchasesOrdersLines=$this->doctrine->getRepository(ERPPurchasesOrdersLines::class);

      $orders_id=$repositoryPurchasesOrders->getPurchasesOrdersByDate();
      $log=fopen("purchaseOrders.txt", "w");
      foreach($orders_id as $order_id){
        $order=$repositoryPurchasesOrders->findOneBy(["id"=>$order_id]);
        $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-checkPurchaseOrder.php?code='.$order->getCode());

        if($result==false){
           $txt="El pedido ".$order->getCode()." no está en Navision \n";
            fwrite($log, $txt);
          }

      }

      fclose($log);

    }


    public function checkSaleOrders(InputInterface $input, OutputInterface $output)
    {

      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-checkSaleOrders.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionGetProducts-checkSaleOrders.lock', 'c');
      }

      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la creación de pedidos en Navision: El proceso ya esta en ejecución.');
        exit;
      }

      //------   Critical Section START   ------
      $repositorySalesOrders=$this->doctrine->getRepository(ERPSalesOrders::class);
      $repositorySalesOrdersLines=$this->doctrine->getRepository(ERPSalesOrdersLines::class);

      $orders_id=$repositorySalesOrders->findNews();
      $log=fopen("saleOrders.txt", "w");
      foreach($orders_id as $order_id){
        $order=$repositorySalesOrders->findOneBy(["id"=>$order_id]);
        $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-checkSale.php?code='.$order->getCode());

        if($result==false){
           $txt="El pedido ".$order->getCode()." no está en Navision \n";
            fwrite($log, $txt);
          }

      }

      fclose($log);

    }

  }
?>
