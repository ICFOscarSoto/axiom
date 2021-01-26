<?php
namespace App\Modules\Prestashop\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPDepartments;
use App\Modules\ERP\Entity\ERPContacts;
use App\Modules\ERP\Entity\ERPAddresses;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPPaymentTerms;
use App\Modules\ERP\Entity\ERPCustomerActivities;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;
use App\Modules\ERP\Entity\ERPCustomerOrdersData;
use App\Modules\ERP\Entity\ERPCustomerCommercialTerms;
use App\Modules\ERP\Entity\ERPSalesOrders;
use App\Modules\ERP\Entity\ERPSalesOrdersLines;
use App\Modules\Carrier\Entity\CarrierCarriers;
use App\Modules\Carrier\Entity\CarrierShippingConditions;
use App\Modules\ERP\Entity\ERPBankAccounts;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class PrestashopGetOrders extends ContainerAwareCommand
{

  private $url="https://www.ferreteriacampollano.com";
  private $token="6TI5549NR221TXMGMLLEHKENMG89C8YV";

  protected function configure(){
        $this
            ->setName('prestashop:getorders')
            ->setDescription('Sync prestashop principal entities')
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
    $output->writeln('Comenzando sincronizacion con Prestashop');
    $output->writeln('==================================');
    switch($entity){
      case 'orders': $this->importOrders($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importOrders(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
         $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-prestashopGetOrders-importOrders.lock', 'c');
     } else {
        $fp = fopen('/tmp/axiom-prestashopGetOrders-importOrders.lock', 'c');
     }

     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de pedidos con prestashop: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $rawSync=false;
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);
     $productRepository=$this->doctrine->getRepository(ERPProducts::class);
     $companyRepository=$this->doctrine->getRepository(GlobaleCompanies::class);
     $company=$companyRepository->findOneBy(["id"=>2]);
     $currency=$repositoryCurrencies->findOneBy(["name"=>"Euro"]);
     $repositoryPaymentMethods=$this->doctrine->getRepository(ERPPaymentMethods::class);
    // $repositorySalesBudgets=$this->doctrine->getRepository(ERPSalesBudgets::class);
  //   $repositorySalesBudgetsLines=$this->doctrine->getRepository(ERPSalesBudgetsLines::class);
     $repositorySalesOrders=$this->doctrine->getRepository(ERPSalesOrders::class);
     $repositorySalesOrdersLines=$this->doctrine->getRepository(ERPSalesOrdersLines::class);
     $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
     $repositoryUsers=$this->doctrine->getRepository(GlobaleUsers::class);
     $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);


     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:orders"]);
     $doctrine = $this->getContainer()->get('doctrine');
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
       $navisionSync->setLastsync(date_create_from_format("Y-m-d H:i:s","2000-01-01 00:00:00"));
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando pedidos prestashop....');


     //	$datetime=new \DateTime();
      //$yesterday=$datetime->format("Y-m-d");
      $tenminutesbefore=date('Y-m-d H:i:s', strtotime('-90 minutes'));
      $now=date('Y-m-d H:i:s');

      $auth = base64_encode($this->token);
      $context = stream_context_create([
          "http" => ["header" => "Authorization: Basic $auth"]
      ]);

    //  $output->writeln($this->url."/api/orders?filter[invoice_date]=['.$tenminutesbefore.','.$now.']&date=1");
      $xml_string=file_get_contents($this->url."/api/orders?display=[id]&filter[invoice_date]=[".$tenminutesbefore.",".$now."]&date=1", false, $context);
      $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
      foreach($xml->orders->order as $order)
      {
        $item=array_unique((array) $order);
    //  dump("Pedido ".$item["id"]);
       $xml_order_string=file_get_contents($this->url."/api/orders/".$item["id"], false, $context);
       $xml_order = simplexml_load_string($xml_order_string, 'SimpleXMLElement', LIBXML_NOCDATA);
       $json = json_encode($xml_order);
       $array = json_decode($json,TRUE);
       $order_array=isset($array["order"])?$array["order"]:[];

    //   dump($order_lines_array);

       $repositorySalesOrders=$this->doctrine->getRepository(ERPSalesOrders::class);
       $salesordersEntity= new ERPSalesOrders();

       $obj=$repositorySalesOrders->findOneBy(["code"=>$order_array["id"]]);
       $cost=0;
       $oldobj=$obj;
       if ($obj==null) {
         $obj=new ERPSalesOrders();
         $obj->setCode($order_array["id"]);
         $obj->setNumber($order_array["id"]);
         $obj->setCompany($company);
         $obj->setCurrency($currency);
         $obj->setDateadd(new \Datetime());
         $obj->setDateupd(new \Datetime());
         $obj->setDeleted(0);
         $obj->setActive(1);
       }

       //customer
      //  $customer=$repositoryCustomers->findOneBy(["code"=>$object["customer"]]);
        $customer=null;
        if($customer==NULL) {
          //$output->writeln('     ! Saltado no existe el cliente');
          //continue;
        }
        $obj->setCustomer(null);


        //payment method
        $payment_methods = array ('redsys' => 'WEB TARJET',
						 'paypal' => 'PAYPAL',
						 'pagantis' => 'PAGANTIS',
						 'fastbay1' => 'PAYPAL',
						 'codfee' => 'CONTRAREEM',
						 'amazon'=> 'PENDIENTE',
						 'cheque' => 'GIRO',
						 'bankwire' => 'TRANSFER',
						 'manomano' => 'MANOMANO',
						 'aliexpress_payment' => 'ALIEXPRESS',
						 'bizum' => 'BIZUM'
           );




        if(isset($payment_methods[$order_array["module"]]))
         		$payment=$payment_methods[$order_array["module"]];
        else $payment=$order_array["module"];

        $paymentmethod=$repositoryPaymentMethods->findOneBy(["paymentcode"=>$payment, "deleted"=>0]);
        $obj->setPaymentmethod($paymentmethod);

/*
        $salesBudget=$repositorySalesBudgets->findOneBy(["code"=>$object["salesbudget"], "deleted"=>0]);
        $obj->setSalesbudget($salesBudget);
*/

        //$author=$repositoryUsers->findOneBy(["email"=>$object["author"]]);
        //if($author==NULL) $author=$repositoryUsers->findOneBy(["name"=>"Administrador"]);
        $author=$repositoryUsers->findOneBy(["name"=>"Administrador"]);
      //  if($object["ship"]==1) $obj->setShipmentdate(date_create_from_format("Y-m-d H:i:s.u",$object["shipmentdate"]["date"])); else $obj->setShipmentdate(null);
        $obj->setShipmentdate(null);
        $obj->setWebsale(1);

      //  $agent=$repositoryUsers->findOneBy(["email"=>$object["agent"]]);
      //  if($agent==NULL) $agent=$author;
        $agent=$author;
        $obj->setAuthor($author);
        $obj->setAgent($agent);


        $obj->setStatus(1);


        $xml_customer_string=file_get_contents($this->url."/api/customers/".$order_array["id_customer"], false, $context);
        $xml_customer = simplexml_load_string($xml_customer_string, 'SimpleXMLElement', LIBXML_NOCDATA);


        $json = json_encode($xml_customer);
        $array = json_decode($json,TRUE);
        $customer_array=isset($array["customer"])?$array["customer"]:[];

        $obj->setCustomername($customer_array["empresa"]?$customer_array["empresa"]:$customer_array["firstname"]." ".$customer_array["lastname"]);
        $obj->setVat(preg_replace("/[^a-zA-Z0-9]/", "", $customer_array["cif"]?$customer_array["cif"]:null));

        //ADDRESS
        $xml_address_invoice_string=file_get_contents($this->url."/api/addresses/".$order_array["id_address_invoice"], false, $context);
        $xml_address_invoice = simplexml_load_string($xml_address_invoice_string, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($xml_address_invoice);
        $array = json_decode($json,TRUE);
        $address_invoice_array=isset($array["address"])?$array["address"]:[];
        $obj->setCustomeraddress($address_invoice_array["address1"]);
        $obj->setCustomercountry(null);
        $obj->setCustomercity($address_invoice_array["city"]);

        //state invoice
        $xml_state_string=file_get_contents($this->url."/api/states/".$address_invoice_array["id_state"], false, $context);
        $xml_state = simplexml_load_string($xml_state_string, 'SimpleXMLElement', LIBXML_NOCDATA);

        $json = json_encode($xml_state);
        $array = json_decode($json,TRUE);
        $state_array=isset($array["state"])?$array["state"]:[];


        $obj->setCustomerstate($state_array["name"]);

        $obj->setCustomerpostcode(substr(preg_replace("/[^a-zA-Z0-9]/", "", $address_invoice_array["postcode"]),0,12));


        //ADDRESS DELIVERY

        //ADDRESS DELIBERY
        $xml_address_delivery_string=file_get_contents($this->url."/api/addresses/".$order_array["id_address_delivery"], false, $context);
        $xml_address_delivery = simplexml_load_string($xml_address_delivery_string, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($xml_address_delivery);
        $array = json_decode($json,TRUE);
        $address_delivery_array=isset($array["address"])?$array["address"]:[];

        $obj->setShiptoname($address_delivery_array["firstname"]." ".$address_delivery_array["lastname"]);
        $obj->setShiptoaddress($address_delivery_array["address1"]);

        $xml_address_delivery_country_string=file_get_contents($this->url."/api/countries/".$address_delivery_array["id_country"], false, $context);
        $xml_address_delivery_country = simplexml_load_string($xml_address_delivery_country_string, 'SimpleXMLElement', LIBXML_NOCDATA);

        $json = json_encode($xml_address_delivery_country);
        $array = json_decode($json,TRUE);
        $address_delivery_country_array=isset($array["country"])?$array["country"]:[];



        $obj->setShiptocountry(null);
        $obj->setShiptocity($address_delivery_array["city"]);

        $xml_state_delivery_string=file_get_contents($this->url."/api/states/".$address_delivery_array["id_state"], false, $context);
        $xml_state_delivery = simplexml_load_string($xml_state_delivery_string, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($xml_state_delivery);
        $array = json_decode($json,TRUE);
        $state_delivery_array=isset($array["state"])?$array["state"]:[];

        $obj->setShiptostate($state_delivery_array["name"]);
        $obj->setShiptopostcode(substr(preg_replace("/[^a-zA-Z0-9]/", "", $address_delivery_array["postcode"]),0,12));

        $obj->setCustomercode("C00003");
    //    dump($order_array["date_add"]);
        $obj->setDate(date_create_from_format("Y-m-d H:i:s",$order_array["date_add"]));
        $obj->setDateofferend(null);

        $obj->setIrpf(0);
        $obj->setIrpfperc(0);
        $obj->setSurcharge(0);
        $obj->setTaxexempt(0);
        $obj->setCost(0);
        $obj->setTotalnet($order_array["total_paid_tax_excl"]);
        $obj->setTotaldto($order_array["total_discounts"]);
        $obj->setTotalbase(0);
        $obj->setTotaltax(0);
        $obj->setTotalsurcharge(0);
        $obj->setTotalirpf(0);
        $obj->setTotal($order_array["total_paid_tax_incl"]);
        $obj->setDateupd(new \Datetime());

      //  dump($obj);

        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();

        //Process lines
        $totalNet=0;
        $totalDto=0;
        $totalBase=0;
        $totalTax=0;
        $totalSurcharge=0;
        $totalIrpf=0;
        $total=0;

        $i=1;
        $aux=[];

        $order_lines_array=$order_array["associations"]["order_rows"]["order_row"];

        //dump($order_lines_array);

        foreach($order_lines_array as $array){
          if(isset($array["id"]))// dump($array["id"]);
          else{
            $aux[]=$array;
        //    dump($aux);
          }

        }
        //$lines=$order_lines_array;
/*
        foreach($lines as $line){
          array_push($array,$line);
        }

        dump($array);

*/
        /*
        foreach($lines as $line){
            $objLine=new ERPSalesOrdersLines();
            $objLine->setSalesorder($obj);
            $objLine->setLinenum($i);
            $objLine->setDateadd(new \Datetime());
            $objLine->setDateupd(new \Datetime());
            $objLine->setDeleted(0);
            $objLine->setActive(1);

            $product=$repositoryProducts->findOneBy(["code"=>$line["product_reference"]]);
            if($product==NULL){
               $output->writeln('     ! Saltado no existe el producto '.$line["product_reference"]);
               continue;
            }
            $i++;
            $objLine->setCode($line["product_reference"]);
            $objLine->setName($line["product_name"]);
            $objLine->setProduct($product);
            $objLine->setUnitprice($line["product_price"]);
            $objLine->setQuantity($line["product_quantity"]);
            $objLine->setCost(0);
            $objLine->setTaxperc(21);
            $objLine->setTaxunit(round(($line["product_price"]*$line["product_quantity"])*21/100,2));
            $objLine->setDtoperc(0);
            $objLine->setDtounit(0);
            $objLine->setSurchargeperc(0);
            $objLine->setSurchargeunit(0);
            $objLine->setSubtotal($line["product_price"]*$line["product_quantity"]);
            $objLine->setTotal($line["product_price"]*$line["product_quantity"]*21);
            $objLine->setDateupd(new \Datetime());
            $this->doctrine->getManager()->persist($objLine);
            $this->doctrine->getManager()->flush();

        }
*/
        $this->doctrine->getManager()->clear();

      }

   }


}
