<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPSalesBudgets;
use App\Modules\ERP\Entity\ERPSalesBudgetsLines;
use App\Modules\ERP\Entity\ERPSalesOrders;
use App\Modules\ERP\Entity\ERPSalesOrdersLines;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPCustomerOrdersData;
use App\Modules\ERP\Entity\ERPCustomerCommercialTerms;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetSalesOrders extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";
  private $ps_url="https://www.ferreteriacampollano.com";
  private $token="6TI5549NR221TXMGMLLEHKENMG89C8YV";

  protected function configure(){
        $this
            ->setName('navision:getsalesorders')
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
      case 'salesorders': $this->importSaleOrders($input, $output);
      break;
      case 'websale': $this->importWebSaleOrders($input, $output);
      break;
      case 'all':
        $this->importSaleOrders($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

  public function importSaleOrders(InputInterface $input, OutputInterface $output){
    //------Sync Sales budgets    ------

    $command = $this->getApplication()->find('navision:getsalesbudgets');
    $arguments = [
        'entity'    => 'salesbudgets'
    ];
    $cmdProductsInput = new ArrayInput($arguments);
    $cmdProductsreturn = $command->run($cmdProductsInput, $output);


    //------   Create Lock Mutex    ------
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetSalesOrders-importSalesOrders.lock', 'c');
    } else {
        $fp = fopen('/tmp/axiom-navisionGetSalesOrders-importSalesOrders.lock', 'c');
    }
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
      $output->writeln('* Fallo al iniciar la sincronizacion de presupuestos: El proceso ya esta en ejecución.');
      exit;
    }

    //------   Critical Section START   ------
    $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"salesOrders"]);
    if ($navisionSync==null) {
      $navisionSync=new NavisionSync();
      $navisionSync->setMaxtimestamp(0);
    }
    $datetime=new \DateTime();
    $output->writeln('* Sincronizando pedidos de venta....');
    $ctx = stream_context_create(array('http'=>
                  array('timeout' => 1800)
                ));
    $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getSalesOrders.php?from='.$navisionSync->getMaxtimestamp(), false, $ctx);
    $objects=json_decode($json, true);
    $objects=$objects[0];
    $repositoryPaymentMethods=$this->doctrine->getRepository(ERPPaymentMethods::class);
    $repositorySalesBudgets=$this->doctrine->getRepository(ERPSalesBudgets::class);
    $repositorySalesBudgetsLines=$this->doctrine->getRepository(ERPSalesBudgetsLines::class);
    $repositorySalesOrders=$this->doctrine->getRepository(ERPSalesOrders::class);
    $repositorySalesOrdersLines=$this->doctrine->getRepository(ERPSalesOrdersLines::class);
    $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
    $repositoryUsers=$this->doctrine->getRepository(GlobaleUsers::class);
    $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);
    $repositoryCustomerGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
    $repositoryPaymentMethod=$this->doctrine->getRepository(ERPPaymentMethods::class);
    $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
    $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
    $repositoryCustomerOrdersData=$this->doctrine->getRepository(ERPCustomerOrdersData::class);
    $repositoryCustomerCommercialTerms=$this->doctrine->getRepository(ERPCustomerCommercialTerms::class);



    //Disable SQL logger
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

    foreach ($objects["class"] as $key=>$object){
    /*  if($object["web"]==1)
      {*/
      $company=$repositoryCompanies->find(2);
      $currency=$repositoryCurrencies->findOneBy(["name"=>"Euro"]);
      $salesBudget=$repositorySalesBudgets->findOneBy(["code"=>$object["salesbudget"], "deleted"=>0]);
      $output->writeln('  - '.$object["code"].' - '.$object["customer"]);
      $obj=$repositorySalesOrders->findOneBy(["code"=>$object["code"]]);
      $cost=0;
      $oldobj=$obj;
      if ($obj==null) {
        $obj=new ERPSalesOrders();
        $obj->setCode($object["code"]);
        $number=intval(substr($object["code"], 5));
        $obj->setNumber($number);
        $obj->setCompany($company);
        $obj->setCurrency($currency);
        $obj->setDateadd(new \Datetime());
        $obj->setDateupd(new \Datetime());
        $obj->setDeleted(0);
        $obj->setActive(1);
      }
       $customer=$repositoryCustomers->findOneBy(["code"=>$object["customer"]]);
       if($customer==NULL AND $object["web"]==1) {
         $customer=$repositoryCustomers->findOneBy(["vat"=>$object["vat"]]);
         if($customer==null) $customer=$repositoryCustomers->findOneBy(["email"=>$object["email"]]);
          if($customer!=null) $obj->setCustomer($customer);
          else{
          $output->writeln('El cliente no existe en Axiom. Vamos a crearlo y asignarlo');
          $auth = base64_encode($this->token);
          $context = stream_context_create([
              "http" => ["header" => "Authorization: Basic $auth"]
          ]);

          $xml_order_string=file_get_contents($this->ps_url."/api/orders/".$object["external_document_number"], false, $context);
          $xml_order = simplexml_load_string($xml_order_string, 'SimpleXMLElement', LIBXML_NOCDATA);
          $json = json_encode($xml_order);
          $array = json_decode($json,TRUE);
          $order_array=isset($array["order"])?$array["order"]:[];
          if($order_array!=NULL) {
            $xml_customer_string=file_get_contents($this->ps_url."/api/customers/".$order_array["id_customer"], false, $context);
            $xml_customer = simplexml_load_string($xml_customer_string, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml_customer);
            $array = json_decode($json,TRUE);
            $customer_array=isset($array["customer"])?$array["customer"]:[];

            $obj_customer=new ERPCustomers();
            $obj_customer->setCode($customer_array["id"]);
            $obj_customer->setCompany($company);
            $obj_customer->setDateadd(new \Datetime());
            $obj_customer->setDateupd(new \Datetime());
            $obj_customer->setDeleted(0);
            $obj_customer->setActive(1);

            if($customer_array["id_default_group"]==2 OR $customer_array["id_default_group"]==3 OR $customer_array["id_default_group"]==6) $axiom_group="GDTO3";
            else if($customer_array["id_default_group"]==8) $axiom_group="GDTO2";
            else if($customer_array["id_default_group"]==7) $axiom_group="GDTO1";
            else $axiom_group="";

            $customergroup=$repositoryCustomerGroups->findOneBy(["name"=>$axiom_group]);

            if($customer_array["cif"]!=null) $obj_customer->setVat($customer_array["cif"]);
            if($customer_array["empresa"]){
               $obj_customer->setName($customer_array["empresa"]);
               $obj_customer->setSocialname($customer_array["empresa"]);
            }
            else {
              $obj_customer->setName($customer_array["firstname"]." ".$customer_array["lastname"]);
              $obj_customer->setSocialname($customer_array["firstname"]." ".$customer_array["lastname"]);
            }
            $output->writeln('Vamos a añadir las direcciones');
            $xml_addresses_string=file_get_contents($this->ps_url."/api/addresses/?display=[id]&filter[id_customer]=".$customer_array["id"], false, $context);
            $xml_addresses = simplexml_load_string($xml_addresses_string, 'SimpleXMLElement', LIBXML_NOCDATA);
            $exist_invoice_address=false;
            if(empty($xml_addresses->addresses->address)){
              $output->writeln('No tiene ninguna dirección asociada');
              $obj_customer->setAddress("desconocido");
              $obj_customer->setPostcode("desconocido");
              $obj_customer->setCity("desconocido");
              $obj_customer->setPhone("desconocido");

            }
            else {
              foreach($xml_addresses->addresses->address as $address){
                if(!$exist_invoice_address){
                  $item=array_unique((array) $address);
                  $xml_invoice_address_string=file_get_contents($this->ps_url."/api/addresses/".$item["id"]."", false, $context);
                  $xml_invoice_address = simplexml_load_string($xml_invoice_address_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                  $json_inv_add = json_encode($xml_invoice_address);
                  $array_inv_add = json_decode($json_inv_add,TRUE);
                  $inv_add_array=isset($array_inv_add["address"])?$array_inv_add["address"]:[];
                  if(!empty($inv_add_array["address2"]) AND $inv_add_array["address2"]!=NULL AND $inv_add_array["address2"][0]!=NULL){
                    $obj_customer->setAddress($inv_add_array["address1"].$inv_add_array["address2"][0]);
                  }
                  else $obj_customer->setAddress($inv_add_array["address1"]);
                  $obj_customer->setCity($inv_add_array["city"]);
                  $obj_customer->setPostcode($inv_add_array["postcode"]);
                  if(!empty($inv_add_array["phone_mobile"])) $obj_customer->setPhone($inv_add_array["phone_mobile"]);
                  else if(!empty($inv_add_array["phone"])) $obj_customer->setPhone($inv_add_array["phone"]);
                  if($inv_add_array["id_country"]==6) $alfa2="ES";
                  else if($inv_add_array["id_country"]==15) $alfa2="PT";
                  $country=$repositoryCountries->findOneBy(["alfa2"=>$alfa2]);
                  $obj_customer->setCountry($country);
                  if($inv_add_array["id_state"]!=0){
                    $xml_state_string=file_get_contents($this->ps_url."/api/states/".$inv_add_array["id_state"], false, $context);
                    $xml_state = simplexml_load_string($xml_state_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                    $json_state = json_encode($xml_state);
                    $array_state = json_decode($json_state,TRUE);
                    $state_array=isset($array_state["state"])?$array_state["state"]:[];
                    $state=$repositoryStates->findOneBy(["isocode"=>$state_array["iso_code"]]);
                    $obj_customer->setState($state);
                  }
                  $bdd_usuario="icf";
                  $bdd_contrasena="6k4mkb9LWT3Zr5A";
                  $bdd_servidor="163.172.113.100";
                  $bdd_base_datos="icf";

                  $connPrestashop=false;
                  $retries=0;
                  while(!$connPrestashop && $retries<3){
                    $connPrestashop = new \mysqli($bdd_servidor, $bdd_usuario, $bdd_contrasena, $bdd_base_datos);
                    $retries++;
                  }
                  if(!$connPrestashop ) {
                     echo "Conexion no se pudo establecer con Prestashop.\r\n";
                     die( print_r( sqlsrv_errors(), true));
                  }
                  $sql='SELECT *
                  FROM fcopc_customer_address
                  WHERE id_address='.$item["id"].' AND object="invoice"';
                  $result = mysqli_query($connPrestashop, $sql);
                  //es la dirección de facturación
                  if($row=mysqli_fetch_object($result)){
                       $exist_invoice_address=true;
                  }
                }
              }
            }//el cliente tiene direcciones
            $obj_customer->setWeb(1);
            $obj_customer->setEmail($customer_array["email"]);
            $obj_customer->setDateupd(new \Datetime());
            $paymentmethod=$repositoryPaymentMethod->findOneBy(["id"=>12]);
            $obj_customer->setPaymentMethod($paymentmethod);//PAGO CONTADO
            $obj_customer->setMinimuminvoiceamount(0);
            $obj_customer->setMaxcredit(0);
            $obj_customer->setPaymentMode("M");//clientes normales de reposición
            //$output->writeln('guardamos cliente');
            $this->doctrine->getManager()->persist($obj_customer);
            $obj->setCustomer($obj_customer);
            $obj->setCustomercode($customer_array["id"]);
            $this->doctrine->getManager()->flush();
            //$output->writeln('Antes de customer '.$customer_array["id"]);
            $customer=$repositoryCustomers->findOneBy(["code"=>$customer_array["id"]]);
            if($customer!=NULL){
              //$output->writeln('Metemos los datos para PEDIDOS');
            /*DATOS PARA PEDIDOS*/
              $ordersData=$repositoryCustomerOrdersData->findOneBy(["customer"=>$customer]);
              if($ordersData==NULL){
                $ordersData=new ERPCustomerOrdersData();
                $ordersData->setCustomer($customer);
                $ordersData->setDateadd(new \Datetime());
                $ordersData->setDateupd(new \Datetime());
                $ordersData->setDeleted(0);
                $ordersData->setActive(1);
              }
              $ordersData->setRequiredordernumber(0);
              $ordersData->setInvoicefordeliverynote(0);
              $ordersData->setPricesdeliverynote(0);
              $ordersData->setPartialshipping(0);
              $ordersData->setAuthorizationcontrol(0);
              //$output->writeln('guardamos ordersdata');
              $this->doctrine->getManager()->persist($ordersData);
              $this->doctrine->getManager()->flush();
              /*DATOS PARA LAS CONDICIONES COMERCIALES*/
              //    $output->writeln('Vamos a añadir condiciones comerciales');
              $commercialterms=$repositoryCustomerCommercialTerms->findOneBy(["customer"=>$customer]);
              if($commercialterms==NULL){
                $commercialterms=new ERPCustomerCommercialTerms();
                $commercialterms->setCustomer($customer);
                $commercialterms->setCustomergroup($customergroup);
                $commercialterms->setDateadd(new \Datetime());
                $commercialterms->setDateupd(new \Datetime());
                $commercialterms->setDeleted(0);
                $commercialterms->setActive(1);
              }
              $commercialterms->setAllowlinediscount(1);
              //  $output->writeln('guardamos comercialterms');
              $this->doctrine->getManager()->persist($commercialterms);
              $this->doctrine->getManager()->flush();
              $obj->setCustomergroup($commercialterms->getCustomergroup());
              }
              else $output->writeln('No encuentra el cliente'.$customer_array["id"]);
          }//el nº de pedido externo no es de un pedido web o directamente no existe
          else $output->writeln('No encuentra información en PS a partir del external_document_number');
          }
       }//el cliente no estaba en Axiom. Hemos tenido que buscarlo en PS, crearlo en Axiom y vincularlo al pedido
       else{
        $obj->setCustomer($customer);
        if($customer!=null) $obj->setCustomercode($customer->getCode());
        $commercialterms=$repositoryCustomerCommercialTerms->findOneBy(["customer"=>$customer]);
        if($commercialterms!=null) $obj->setCustomergroup($commercialterms->getCustomergroup());
        }//existe el cliente asociado al pedido
       $paymentmethod=$repositoryPaymentMethods->findOneBy(["paymentcode"=>$object["paymentcode"], "deleted"=>0]);
       $obj->setPaymentmethod($paymentmethod);

       $salesBudget=$repositorySalesBudgets->findOneBy(["code"=>$object["salesbudget"], "deleted"=>0]);
       $obj->setSalesbudget($salesBudget);

       $author=$repositoryUsers->findOneBy(["email"=>$object["author"]]);
       if($author==NULL) $author=$repositoryUsers->findOneBy(["name"=>"Sistemas Ferretería Campollano"]);

       if($object["ship"]==1) $obj->setShipmentdate(date_create_from_format("Y-m-d H:i:s.u",$object["shipmentdate"]["date"])); else $obj->setShipmentdate(null);
       $obj->setWebsale($object["web"]);

       $agent=$repositoryUsers->findOneBy(["email"=>$object["agent"]]);
       if($agent==NULL) $agent=$author;
       $obj->setAuthor($author);
       $obj->setAgent($agent);
       $obj->setVat(preg_replace("/[^a-zA-Z0-9]/", "", $object["vat"]));
       $obj->setStatus($object["status"]);

       $obj->setCustomername($object["customername"]);
       $obj->setCustomeraddress($object["customeraddress"]);
       $obj->setCustomercountry($customer?$customer->getCountry():null);
       $obj->setCustomercity($object["customercity"]);
       $obj->setCustomerstate($customer?$customer->getState()!=null?$customer->getState()->getName():null:null);
       $obj->setCustomerpostcode(substr(preg_replace("/[^a-zA-Z0-9]/", "", $object["customerpostcode"]),0,12));

       $obj->setShiptoname($object["shiptoname"]);
       $obj->setShiptoaddress($object["shiptoaddress"]);
       $obj->setShiptocountry($customer?$customer->getCountry():null);
       $obj->setShiptocity($object["shiptocity"]);
       $obj->setShiptostate($customer?$customer->getState()!=null?$customer->getState()->getName():null:null);
       $obj->setShiptopostcode(substr(preg_replace("/[^a-zA-Z0-9]/", "", $object["shiptopostcode"]),0,12));

       //$obj->setCustomercode($object["customer"]);

       $obj->setDate(date_create_from_format("Y-m-d H:i:s.u",$object["date"]["date"]));
       $obj->setDateofferend(date_create_from_format("Y-m-d H:i:s.u",$object["enddate"]["date"]));

       $obj->setIrpf(0);
       $obj->setIrpfperc(0);
       $obj->setSurcharge(0);
       $obj->setTaxexempt(0);
       $obj->setCost(round($object["cost"],2));
       $obj->setTotalnet($object["linestotal"]);
       $obj->setTotaldto($object["dto"]);
       $obj->setTotalbase($object["base"]);
       $obj->setTotaltax($object["vattotal"]);
       $obj->setTotalsurcharge(0);
       $obj->setTotalirpf(0);
       $obj->setTotal($object["total"]);
       $obj->setExternalordernumber($object["external_document_number"]);
       $obj->setDateupd(new \Datetime());

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
       foreach($object["lines"] as $key=>$line){
        $output->writeln('      -> Linea '.$line["linenum"].' - '.$line["reference"]);
        $objLine=$repositorySalesOrdersLines->findOneBy(["salesorder"=>$obj,"linenum"=>$line["linenum"]]);
        if ($objLine==null) {
          $objLine=new ERPSalesOrdersLines();
          $objLine->setSalesorder($obj);
          $objLine->setLinenum($line["linenum"]);
          $objLine->setDateadd(new \Datetime());
          $objLine->setDateupd(new \Datetime());
          $objLine->setDeleted(0);
          $objLine->setActive(1);
        }
        $product=$repositoryProducts->findOneBy(["code"=>$line["reference"]]);
        if($product==NULL){
           $output->writeln('     ! Saltado no existe el producto');
           continue;
        }
        $objLine->setCode($line["reference"]);
        $objLine->setName($line["description"]);
        $objLine->setProduct($product);
        $objLine->setUnitprice($line["price"]);
        $objLine->setQuantity($line["quantity"]);
        $objLine->setCost(round($line["cost"],2));
        $objLine->setTaxperc($line["taxperc"]);
        $objLine->setTaxunit(round($line["linetotal"]*$line["taxperc"]/100,2));
        $objLine->setDtoperc($line["discountperc"]);
        $objLine->setDtounit($line["discounttotal"]);
        $objLine->setSurchargeperc($line["surchargeperc"]);
        $objLine->setSurchargeunit(round($line["linetotal"]*$line["surchargeperc"]/100,2));
        $objLine->setSubtotal($line["price"]*$line["quantity"]);
        $objLine->setTotal($line["total"]);
        $objLine->setDateupd(new \Datetime());
        $this->doctrine->getManager()->persist($objLine);
        $this->doctrine->getManager()->flush();
        //$output->writeln('     - Grabado ID: '.$objLine->getId());
      }
    //  }//solo pedidos web
      }
      //$totalBase=$totalNet-$totalDto;
      //$this->doctrine->getManager()->flush();
    $this->doctrine->getManager()->clear();

    if($objects["maxtimestamp"]>0){
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"salesOrders"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setEntity("salesOrders");
      }
      $navisionSync->setLastsync($datetime);
      $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
      $this->doctrine->getManager()->persist($navisionSync);
      $this->doctrine->getManager()->flush();
    }
    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);
  }

    public function importWebSaleOrders(InputInterface $input, OutputInterface $output){
      //------   Create Lock Mutex    ------
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetSalesOrders-importSalesOrders.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionGetSalesOrders-importSalesOrders.lock', 'c');
      }
      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la sincronizacion de presupuestos: El proceso ya esta en ejecución.');
        exit;
      }

      //------   Critical Section START   ------
      $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"salesOrders"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setMaxtimestamp(0);
      }
      $datetime=new \DateTime();
      $output->writeln('* Sincronizando pedidos de venta....');
      $ctx = stream_context_create(array('http'=>
                    array('timeout' => 1800)
                  ));
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getSalesOrders.php?from='.$navisionSync->getMaxtimestamp(), false, $ctx);
      $objects=json_decode($json, true);
      $objects=$objects[0];
      $repositoryPaymentMethods=$this->doctrine->getRepository(ERPPaymentMethods::class);
      $repositorySalesBudgets=$this->doctrine->getRepository(ERPSalesBudgets::class);
      $repositorySalesBudgetsLines=$this->doctrine->getRepository(ERPSalesBudgetsLines::class);
      $repositorySalesOrders=$this->doctrine->getRepository(ERPSalesOrders::class);
      $repositorySalesOrdersLines=$this->doctrine->getRepository(ERPSalesOrdersLines::class);
      $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
      $repositoryUsers=$this->doctrine->getRepository(GlobaleUsers::class);
      $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
      $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
      $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);

      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

      foreach ($objects["class"] as $key=>$object){
        $output->writeln('  - '.$object["code"].' - '.$object["customer"]);
        $obj=$repositorySalesOrders->findOneBy(["code"=>$object["code"]]);
        $cost=0;
        $oldobj=$obj;
        if ($obj==null) {
          continue;
        }

         if($object["ship"]==1) $obj->setShipmentdate(date_create_from_format("Y-m-d H:i:s.u",$object["shipmentdate"]["date"])); else $obj->setShipmentdate(null);
         $obj->setWebsale($object["web"]);
         $this->doctrine->getManager()->persist($obj);
         $this->doctrine->getManager()->flush();



      }
      if($objects["maxtimestamp"]>0){
        $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"salesOrders"]);
        if ($navisionSync==null) {
          $navisionSync=new NavisionSync();
          $navisionSync->setEntity("salesOrders");
        }
        $navisionSync->setLastsync($datetime);
        $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
        $this->doctrine->getManager()->persist($navisionSync);
        $this->doctrine->getManager()->flush();
      }
      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
    }
}


?>
