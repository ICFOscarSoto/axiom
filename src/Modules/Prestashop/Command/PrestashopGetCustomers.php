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


class PrestashopGetCustomers extends ContainerAwareCommand
{

  private $url="https://www.ferreteriacampollano.com";
  private $token="6TI5549NR221TXMGMLLEHKENMG89C8YV";

  protected function configure(){
        $this
            ->setName('prestashop:getcustomers')
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
        case 'importcustomers': $this->importCustomers($input, $output);
          break;
        case 'updatecustomers': $this->updateCustomers($input, $output);
            break;
        default:
          $output->writeln('Opcion no válida');
        break;
      }

    }


    public function importCustomers(InputInterface $input, OutputInterface $output){
      //------   Create Lock Mutex    ------
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-prestashopGetCustomers-importCustomers.lock', 'c');
      } else {
         $fp = fopen('/tmp/axiom-prestashopGetCustomers-importCustomers.lock', 'c');
      }

      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la sincronizacion de clientes con prestashop: El proceso ya esta en ejecución.');
        exit;
      }

      $rawSync=false;
      $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
      $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);
      $companyRepository=$this->doctrine->getRepository(GlobaleCompanies::class);
      $company=$companyRepository->findOneBy(["id"=>2]);
      $currency=$repositoryCurrencies->findOneBy(["name"=>"Euro"]);
      $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
      $repositoryCustomerGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
      $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
      $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
      $repositoryPaymentMethod=$this->doctrine->getRepository(ERPPaymentMethods::class);
      $repositoryCustomerOrdersData=$this->doctrine->getRepository(ERPCustomerOrdersData::class);
      $repositoryCustomerCommercialTerms=$this->doctrine->getRepository(ERPCustomerCommercialTerms::class);

      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:customers"]);
      $doctrine = $this->getContainer()->get('doctrine');
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setMaxtimestamp(0);
        $navisionSync->setLastsync(date_create_from_format("Y-m-d H:i:s","2000-01-01 00:00:00"));
      }
      $datetime=new \DateTime();
      $output->writeln('* Sincronizando clientes prestashop....');


      $auth = base64_encode($this->token);
      $context = stream_context_create([
          "http" => ["header" => "Authorization: Basic $auth"]
      ]);

      $twelveminutesbefore=date('Y-m-d H:i:s', strtotime('-12 minutes'));
      $now=date('Y-m-d H:i:s');

    //  $output->writeln($this->url."/api/orders?filter[invoice_date]=['.$tenminutesbefore.','.$now.']&date=1");
    //  $xml_string=file_get_contents($this->url."/api/customers/?display=[id]&filter[date_add]=[".$twelveminutesbefore.",".$now."]&date=1", false, $context);
      $xml_string=file_get_contents($this->url."/api/customers/?display=[id]&filter[date_add]=[2019-01-01,2019-12-31]&filter[id_default_group]=8&date=1", false, $context);
      $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
      $maxtimestamp=0;
      foreach($xml->customers->customer as $customer)
      {

        $item=array_unique((array) $customer);
      //  if($item["id"]=="34059")
      //  {

        //  dump("Pedido ".$item["id"]);
           $xml_customer_string=file_get_contents($this->url."/api/customers/".$item["id"], false, $context);
           $xml_customer = simplexml_load_string($xml_customer_string, 'SimpleXMLElement', LIBXML_NOCDATA);
           //obtenemos el id del grupo de cliente
           $id_group=$xml_customer->customer->associations->groups->group;
           $array_id_group=array_unique((array) $id_group);

           $json = json_encode($xml_customer);
           $array = json_decode($json,TRUE);
           $customer_array=isset($array["customer"])?$array["customer"]:[];

           $date=new \Datetime($customer_array["date_upd"]);
           $newTimestamp= $date->getTimestamp();
           if($newTimestamp>$maxtimestamp) $maxtimestamp=$newTimestamp;

           if(($customer_array["id_customer_icf"]==null OR $customer_array["id_customer_icf"]=='') AND strpos($customer_array["email"],"ebay")==false AND strpos($customer_array["email"],"manomano")==false)
           {
             $customerentity=new ERPCustomers();

             $obj=$repositoryCustomers->findOneBy(["email"=>$customer_array["email"]]);
                      /*
            Si no existe en axiom ningún cliente con ese email o estamos ante un usuario de grupo "cliente/GDTO3/GDTO2/GDTO1", entonces lo insertamos en Axiom.
            Este filtro lo ponemos porque es posible que ya exista una referencia a ese usuario en la BDD de algún pedido anterior
            que haya hecho como invitado. En este caso, le daremos preferencia a los datos del usuario en su cuenta registrada como como "cliente/GDTO3/GDTO2/GDTO1".
            */
          if ($obj==null OR $array_id_group["id"]==3 OR $array_id_group["id"]==6 OR $array_id_group["id"]==7 OR $array_id_group["id"]==8) {
               $output->writeln('Insertando el cliente '.$item["id"]);
               if($obj!=null AND $array_id_group["id"]==3)  $output->writeln('Ya existía en Axiom pero como invitado');

               if($obj==null) $obj=new ERPCustomers();
               $obj->setCode($customer_array["id"]);
               $obj->setCompany($company);
               $obj->setDateadd(new \Datetime());
               $obj->setDateupd(new \Datetime());
               $obj->setDeleted(0);
               $obj->setActive(1);

              if($customer_array["id_default_group"]==2 OR $customer_array["id_default_group"]==3 OR $customer_array["id_default_group"]==6) $axiom_group="GDTO3";
              else if($customer_array["id_default_group"]==8) $axiom_group="GDTO2";
              else if($customer_array["id_default_group"]==7) $axiom_group="GDTO1";
              else $axiom_group="";

              $customergroup=$repositoryCustomerGroups->findOneBy(["name"=>$axiom_group]);

            //  $obj->setActive(1);
              if($customer_array["cif"]!=null) $obj->setVat($customer_array["cif"]);
              if($customer_array["empresa"]){
                 $obj->setName($customer_array["empresa"]);
                 $obj->setSocialname($customer_array["empresa"]);

              }
              else {
                $obj->setName($customer_array["firstname"]." ".$customer_array["lastname"]);
                $obj->setSocialname($customer_array["firstname"]." ".$customer_array["lastname"]);
              }

              $xml_addresses_string=file_get_contents($this->url."/api/addresses/?display=[id]&filter[id_customer]=".$customer_array["id"], false, $context);
              $xml_addresses = simplexml_load_string($xml_addresses_string, 'SimpleXMLElement', LIBXML_NOCDATA);
              $exist_invoice_address=false;
              if(empty($xml_addresses->addresses->address)){
                $output->writeln('No tiene ninguna dirección asociada');
                $obj->setAddress("desconocido");
                $obj->setPostcode("desconocido");
                $obj->setCity("desconocido");
                $obj->setPhone("desconocido");

              }

              else {

                foreach($xml_addresses->addresses->address as $address)
                {
                  if(!$exist_invoice_address)
                  {
                      $item=array_unique((array) $address);

                      $xml_invoice_address_string=file_get_contents($this->url."/api/addresses/".$item["id"]."", false, $context);
                      $xml_invoice_address = simplexml_load_string($xml_invoice_address_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                      $json_inv_add = json_encode($xml_invoice_address);
                      $array_inv_add = json_decode($json_inv_add,TRUE);
                      $inv_add_array=isset($array_inv_add["address"])?$array_inv_add["address"]:[];
                      if(!empty($inv_add_array["address2"]))  $obj->setAddress($inv_add_array["address1"].$inv_add_array["address2"]);
                      else $obj->setAddress($inv_add_array["address1"]);
                      $obj->setCity($inv_add_array["city"]);
                      $obj->setPostcode($inv_add_array["postcode"]);
                      if(!empty($inv_add_array["phone_mobile"])) $obj->setPhone($inv_add_array["phone_mobile"]);
                      else if(!empty($inv_add_array["phone"])) $obj->setPhone($inv_add_array["phone"]);


                      if($inv_add_array["id_country"]==6) $alfa2="ES";
                      else if($inv_add_array["id_country"]==15) $alfa2="PT";
                      $country=$repositoryCountries->findOneBy(["alfa2"=>$alfa2]);
                      $obj->setCountry($country);
                      if($inv_add_array["id_state"]!=0)
                      {
                        $xml_state_string=file_get_contents($this->url."/api/states/".$inv_add_array["id_state"], false, $context);
                        $xml_state = simplexml_load_string($xml_state_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                        $json_state = json_encode($xml_state);
                        $array_state = json_decode($json_state,TRUE);
                        $state_array=isset($array_state["state"])?$array_state["state"]:[];
                        $state=$repositoryStates->findOneBy(["isocode"=>$state_array["iso_code"]]);
                        $obj->setState($state);
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

              $obj->setWeb(1);
              $obj->setEmail($customer_array["email"]);
              $obj->setDateupd(new \Datetime());
              $paymentmethod=$repositoryPaymentMethod->findOneBy(["id"=>12]);

              $obj->setPaymentMethod($paymentmethod);//PAGO CONTADO
              $obj->setMinimuminvoiceamount(0);
              $obj->setMaxcredit(0);
              $obj->setPaymentMode("M");//clientes normales de reposición

            //    $output->writeln('guardamos cliente');
              $this->doctrine->getManager()->persist($obj);
              $this->doctrine->getManager()->flush();


              $customer=$repositoryCustomers->findOneBy(["code"=>$customer_array["id"]]);

              if($customer!=NULL){
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
              //    $output->writeln('guardamos ordersdata');
                  $this->doctrine->getManager()->persist($ordersData);
                  $this->doctrine->getManager()->flush();


                  /*DATOS PARA LAS CONDICIONES COMERCIALES*/
                  $commercialterms=$repositoryCustomerCommercialTerms->findOneBy(["customer"=>$customer]);

                  if($commercialterms==NULL)
                  {
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
           }

         }//no está en Axiom
         else{
           $output->writeln('Este cliente ya está incluido en Axiom');

         }
       }//no está en Navision


  //      }//if de pruebas
    }//foreach customers
    $this->doctrine->getManager()->clear();

    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:customers"]);
    if ($navisionSync==null) {
      $navisionSync=new NavisionSync();
      $navisionSync->setEntity("prestashop:customers");
    }

    $navisionSync->setLastsync($datetime);
    $navisionSync->setMaxtimestamp($maxtimestamp);
    $this->doctrine->getManager()->persist($navisionSync);
    $this->doctrine->getManager()->flush();
    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);


    }


    public function updateCustomers(InputInterface $input, OutputInterface $output){
      //------   Create Lock Mutex    ------
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-prestashopGetCustomers-updateCustomers.lock', 'c');
      } else {
         $fp = fopen('/tmp/axiom-prestashopGetCustomers-updateCustomers.lock', 'c');
      }

      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la actualización de clientes de prestashop: El proceso ya esta en ejecución.');
        exit;
      }

      $rawSync=false;
      $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
      $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);
      $companyRepository=$this->doctrine->getRepository(GlobaleCompanies::class);
      $company=$companyRepository->findOneBy(["id"=>2]);
      $currency=$repositoryCurrencies->findOneBy(["name"=>"Euro"]);
      $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
      $repositoryCustomerGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
      $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
      $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
      $repositoryPaymentMethod=$this->doctrine->getRepository(ERPPaymentMethods::class);
      $repositoryCustomerOrdersData=$this->doctrine->getRepository(ERPCustomerOrdersData::class);
      $repositoryCustomerCommercialTerms=$this->doctrine->getRepository(ERPCustomerCommercialTerms::class);

      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:customers"]);
      $doctrine = $this->getContainer()->get('doctrine');
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setMaxtimestamp(0);
        $navisionSync->setLastsync(date_create_from_format("Y-m-d H:i:s","2000-01-01 00:00:00"));
      }
      $datetime=new \DateTime();
      $output->writeln('* Actualizando clientes prestashop....');


      $auth = base64_encode($this->token);
      $context = stream_context_create([
          "http" => ["header" => "Authorization: Basic $auth"]
      ]);

    //  $lasttimestamp=date('Y-m-d H:i:s', $navisionSync->getMaxtimestamp());

      //$timestamp = 1465298940;
      $datetimeFormat = 'Y-m-d H:i:s';

      $date = new \DateTime();
      // If you must have use time zones
      // $date = new \DateTime('now', new \DateTimeZone('Europe/Helsinki'));
      $date->setTimestamp($navisionSync->getMaxtimestamp());
      $lasttimestamp=$date->format($datetimeFormat);

      $now=date('Y-m-d H:i:s');
    //  $output->writeln($lasttimestamp);
      //$output->writeln($this->url."/api/customers/?display=[id]&filter[date_upd]=[".$lasttimestamp.",".$now."]&date=1");
    //  $xml_string=file_get_contents($this->url."/api/customers/?display=[id]&filter[date_add]=[".$twelveminutesbefore.",".$now."]&date=1", false, $context);
      $xml_string=file_get_contents($this->url."/api/customers/?display=[id]&filter[date_upd]=[".$lasttimestamp.",".$now."]&date=1", false, $context);
      $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);

      $maxtimestamp=$lasttimestamp;
      foreach($xml->customers->customer as $customer)
      {

        $item=array_unique((array) $customer);
      //  if($item["id"]=="34059")
      //  {

        //  dump("Pedido ".$item["id"]);
           $xml_customer_string=file_get_contents($this->url."/api/customers/".$item["id"], false, $context);
           $xml_customer = simplexml_load_string($xml_customer_string, 'SimpleXMLElement', LIBXML_NOCDATA);
           //obtenemos el id del grupo de cliente
           $id_group=$xml_customer->customer->associations->groups->group;
           $array_id_group=array_unique((array) $id_group);

           $json = json_encode($xml_customer);
           $array = json_decode($json,TRUE);
           $customer_array=isset($array["customer"])?$array["customer"]:[];


           $date=new \Datetime($customer_array["date_upd"]);
           $newTimestamp= $date->getTimestamp();
           if($newTimestamp>$maxtimestamp) $maxtimestamp=$newTimestamp;


           if(($customer_array["id_customer_icf"]==null OR $customer_array["id_customer_icf"]=='') AND strpos($customer_array["email"],"ebay")==false AND strpos($customer_array["email"],"manomano")==false)
           {
             $customerentity=new ERPCustomers();

             $obj=$repositoryCustomers->findOneBy(["email"=>$customer_array["email"]]);
                      /*
            Si no existe en axiom ningún cliente con ese email o estamos ante un usuario de grupo "cliente/GDTO3/GDTO2/GDTO1", entonces lo insertamos en Axiom.
            Este filtro lo ponemos porque es posible que ya exista una referencia a ese usuario en la BDD de algún pedido anterior
            que haya hecho como invitado. En este caso, le daremos preferencia a los datos del usuario en su cuenta registrada como como "cliente/GDTO3/GDTO2/GDTO1".
            */
          //if ($obj==null OR $array_id_group["id"]==3 OR $array_id_group["id"]==6 OR $array_id_group["id"]==7 OR $array_id_group["id"]==8) {
               $output->writeln('Actualizando el cliente '.$item["id"]);
              // if($obj!=null AND $array_id_group["id"]==3)  $output->writeln('Ya existía en Axiom pero como invitado');

               if($obj==null) $obj=new ERPCustomers();
               $obj->setCode($customer_array["id"]);
               $obj->setCompany($company);
               $obj->setDateadd(new \Datetime());
               $obj->setDateupd(new \Datetime());
               $obj->setDeleted(0);
               $obj->setActive(1);

              if($customer_array["id_default_group"]==2 OR $customer_array["id_default_group"]==3 OR $customer_array["id_default_group"]==6) $axiom_group="GDTO3";
              else if($customer_array["id_default_group"]==8) $axiom_group="GDTO2";
              else if($customer_array["id_default_group"]==7) $axiom_group="GDTO1";
              else $axiom_group="GDTO3";

              $customergroup=$repositoryCustomerGroups->findOneBy(["name"=>$axiom_group]);

              $obj->setActive(1);
              if($customer_array["cif"]!=null) $obj->setVat($customer_array["cif"]);
              if($customer_array["empresa"]){
                 $obj->setName($customer_array["empresa"]);
                 $obj->setSocialname($customer_array["empresa"]);

              }
              else {
                $obj->setName($customer_array["firstname"].$customer_array["lastname"]);
                $obj->setSocialname($customer_array["firstname"].$customer_array["lastname"]);
              }

              $xml_addresses_string=file_get_contents($this->url."/api/addresses/?display=[id]&filter[id_customer]=".$customer_array["id"], false, $context);
              $xml_addresses = simplexml_load_string($xml_addresses_string, 'SimpleXMLElement', LIBXML_NOCDATA);
              $exist_invoice_address=false;
              if(empty($xml_addresses->addresses->address)){
                $output->writeln('No tiene ninguna dirección asociada');
                $obj->setAddress("desconocido");
                $obj->setPostcode("desconocido");
                $obj->setCity("desconocido");
                $obj->setPhone("desconocido");

              }

              else {

                foreach($xml_addresses->addresses->address as $address)
                {
                  if(!$exist_invoice_address)
                  {
                      $item=array_unique((array) $address);

                      $xml_invoice_address_string=file_get_contents($this->url."/api/addresses/".$item["id"]."", false, $context);
                      $xml_invoice_address = simplexml_load_string($xml_invoice_address_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                      $json_inv_add = json_encode($xml_invoice_address);
                      $array_inv_add = json_decode($json_inv_add,TRUE);
                      $inv_add_array=isset($array_inv_add["address"])?$array_inv_add["address"]:[];
                      if(!empty($inv_add_array["address2"]))  $obj->setAddress($inv_add_array["address1"].$inv_add_array["address2"]);
                      else $obj->setAddress($inv_add_array["address1"]);
                      $obj->setCity($inv_add_array["city"]);
                      $obj->setPostcode($inv_add_array["postcode"]);
                      if(!empty($inv_add_array["phone_mobile"])) $obj->setPhone($inv_add_array["phone_mobile"]);
                      else if(!empty($inv_add_array["phone"])) $obj->setPhone($inv_add_array["phone"]);


                      if($inv_add_array["id_country"]==6) $alfa2="ES";
                      else if($inv_add_array["id_country"]==15) $alfa2="PT";
                      $country=$repositoryCountries->findOneBy(["alfa2"=>$alfa2]);
                      $obj->setCountry($country);
                      if($inv_add_array["id_state"]!=0)
                      {
                        $xml_state_string=file_get_contents($this->url."/api/states/".$inv_add_array["id_state"], false, $context);
                        $xml_state = simplexml_load_string($xml_state_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                        $json_state = json_encode($xml_state);
                        $array_state = json_decode($json_state,TRUE);
                        $state_array=isset($array_state["state"])?$array_state["state"]:[];
                        $state=$repositoryStates->findOneBy(["isocode"=>$state_array["iso_code"]]);
                        $obj->setState($state);
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

              $obj->setWeb(1);
              $obj->setEmail($customer_array["email"]);
              $obj->setDateupd(new \Datetime());
              $paymentmethod=$repositoryPaymentMethod->findOneBy(["id"=>12]);

              $obj->setPaymentMethod($paymentmethod);//PAGO CONTADO
              $obj->setMinimuminvoiceamount(0);
              $obj->setMaxcredit(0);
              $obj->setPaymentMode("M");//clientes normales de reposición

            //    $output->writeln('guardamos cliente');
              $this->doctrine->getManager()->persist($obj);
              $this->doctrine->getManager()->flush();


              $customer=$repositoryCustomers->findOneBy(["code"=>$customer_array["id"]]);

              if($customer!=NULL){
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
              //    $output->writeln('guardamos ordersdata');
                  $this->doctrine->getManager()->persist($ordersData);
                  $this->doctrine->getManager()->flush();


                  /*DATOS PARA LAS CONDICIONES COMERCIALES*/
                  $commercialterms=$repositoryCustomerCommercialTerms->findOneBy(["customer"=>$customer]);

                  if($commercialterms==NULL)
                  {
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
           }

      //   }//no está en Axiom
/*
         else{
           $output->writeln('Este cliente ya está incluido en Axiom');

         }*/
       }//no está en Navision


  //      }//if de pruebas
    }//foreach customers
    $this->doctrine->getManager()->clear();

    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:customers"]);
    if ($navisionSync==null) {
      $navisionSync=new NavisionSync();
      $navisionSync->setEntity("prestashop:customers");
    }

    $navisionSync->setLastsync($datetime);
    $navisionSync->setMaxtimestamp($maxtimestamp);

    $this->doctrine->getManager()->persist($navisionSync);
    $this->doctrine->getManager()->flush();
    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);


    }
}
