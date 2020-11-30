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
    $orders=$repositoryPurchasesOrders->findBy(["code"=>"20PC09132"]);


    foreach($orders as $order){
      if ($order->getAuthor()->getName()=="Administrador") $author=null;
      else $author=$order->getAuthor()->getEmail();


      if (strpos($order->getCode(),'20PC')) $devolucion=0;
      else $devolucion=1;
      $orderJson=["No."=>$order->getCode(),
      "Buy-from Vendor No."=>$order->getSupplier()->getCode(),
      "Assigned User ID"=>$author,
      "Purchaser Code"=>$order->getAgent()->getEmail(),
      "Buy-from Vendor Name"=>substr($order->getSuppliername(),0,50),
      "Buy-from Vendor Name 2"=>substr($order->getSuppliername(),50,50),
      "Buy-from Address"=>substr($order->getSupplieraddress(),0,50),
      "Buy-from Address 2"=>substr($order->getSupplieraddress(),50,50),
      "Buy-from Post Code"=>$order->getSupplierpostcode(),
      "Buy-from City"=>$order->getSuppliercity(),
      "Buy-from County"=>$order->getSupplierstate(),
      "No oferta relacionada"=>$order->getPurchasesbudget()?$order->getPurchasesbudget()->getCode():'',
      "Ship-to Post Code"=>$order->getSupplierpostcode(),
      "Status"=>$order->getStatus(),
      "Payment Method Code"=>$order->getSupplier()->getPaymentmethod()->getPaymentcode(),
      "VAT Registration No."=>$order->getVat(),
      "Document Date" => $order->getDate(),
      "Fecha 1.lanzamiento" => $order->getDateofferend(),
      "Es Devolucion"=>$devolucion,
      "Order Date"=>$order->getDateadd()

      ];

    //  $output->writeln(json_encode($orderJson));

      $orderLinesArray=[];

      $orderlines=$repositoryPurchasesOrdersLines->findBy(["purchasesorder"=>$order]);

      foreach($orderlines as $orderline){
      //  $productrepository=$doctrine->getRepository(ERPProducts::class);
      //$product=$productrepository->getOneBy(["id"=>$orderline->getProduct()->getId()]);

        $quantity=$orderline->getQuantity();
        $unitprice=$orderline->getUnitprice();
        $total=$orderline->getTotal();
        $dto=$orderline->getDtoperc();
        $linenum=$orderline->getLinenum()*10000;
        $line[]=[
          "No."=>$orderline->getCode(),
          "Document No."=>$order->getCode(),
        /*  "Cross-Reference No."=>,*/
          "Description"=>substr($orderline->getName(),0,50),
          "Description 2"=>substr($orderline->getName(),50,50)?substr($orderline->getName(),50,50):"",
          "Quantity"=>$quantity,
          "Outstanding Quantity"=>$quantity,
          "Line Discount %"=>$dto,
          "Line Discount Amount"=>$orderline->getDtounit(),
          "Amount"=>round($total/1.21,0),
          "Amount including VAT"=>$total,
          "Line No."=>$linenum,
          /*"EC %" vat,*/
          "VAT %"=>$orderline->getTaxperc(),
          "Direct Unit Cost"=>$unitprice,
          "Unit price (LCY)"=>$unitprice,
          "Unit Cost (LCY)"=>round(($total/1.21)/$quantity,0),
          "Unit Cost"=>round(($total/1.21)/$quantity,0),
          "Line Discount Amount"=>($unitprice*$quantity)*($dto/100)
        ];

        $orderLinesArray=$line;

      }

      $orderJson["lines"]=$orderLinesArray;
      echo json_encode($orderJson);

/*
      $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createPurchasesOrders.php?json='.urlencode(json_encode($orderJson)));
*/
    }

    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);

  }



}
?>
