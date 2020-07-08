<?php
namespace App\Modules\Navision\Command;

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
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetCustomers extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getcustomers')
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
      case 'customers': $this->importCustomer($input, $output);
      break;
      case 'all':
        $this->importCustomer($input, $output);
      break;
      case 'comments':
        $this->importCustomerComment($input, $output);
      break;
      case 'contacts':
        $this->importCustomerContact($input, $output);
      break;
      case 'addresses':
          $this->importCustomerAddresses($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importCustomer(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
         $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetCustomers-importCustomer.lock', 'c');
     } else {
        $fp = fopen('/tmp/axiom-navisionGetCustomers-importCustomer.lock', 'c');
     }

     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de clientes: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customers"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando clientes....');
     $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getCustomers.php?from='.$navisionSync->getMaxtimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     //dump($products["products"]);
     $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
     $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);
     $repositoryPaymentMethod=$this->doctrine->getRepository(ERPPaymentMethods::class);
     $repositoryPaymentTerms=$this->doctrine->getRepository(ERPPaymentTerms::class);
     $repositoryCustomerOrdersData=$this->doctrine->getRepository(ERPCustomerOrdersData::class);
     $repositoryCustomerCommercialTerms=$this->doctrine->getRepository(ERPCustomerCommercialTerms::class);
     $repositoryCustomerActivities=$this->doctrine->getRepository(ERPCustomerActivities::class);
     $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
     $repositoryCustomerGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
     $repositoryCarriers=$this->doctrine->getRepository(CarrierCarriers::class);
     $repositoryCarrierShippingConditions=$this->doctrine->getRepository(CarrierShippingConditions::class);
     $repositoryBankAccounts=$this->doctrine->getRepository(ERPBankAccounts::class);
     $repository=$this->doctrine->getRepository(ERPCustomers::class);
     //Disable SQL logger
     $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
     $log=fopen("WrongSwiftCodes.txt", "w");
     foreach ($objects["class"] as $key=>$object){
       $output->writeln('  - '.$object["code"].' - '.$object["socialname"]);
       if($object["vat"]==null) continue;
       $obj=$repository->findOneBy(["code"=>$object["code"]]);
       if ($obj==null) {
         $obj=new ERPCustomers();
         $obj->setCode($object["code"]);
         $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
         $company=$repositoryCompanies->find(2);
         $obj->setCompany($company);
         $obj->setDateadd(new \Datetime());
         $obj->setDeleted(0);
         $obj->setActive(1);
       }
        $country=$repositoryCountries->findOneBy(["alfa2"=>$object["country"]]);
        $state=$repositoryStates->findOneBy(["name"=>$object["state"]]);
        $currency=$repositoryCurrencies->findOneBy(["isocode"=>"EUR"]);
        $paymentMethod=$repositoryPaymentMethod->findOneBy(["paymentcode"=>$object["payment_method"]]);
        $activity=$repositoryCustomerActivities->findOneBy(["code"=>$object["activity"]]);
        if($object["customergroup"]=="GDTO4" OR $object["customergroup"]=="GDTO5" OR $object["customergroup"]=="" ) $axiom_group="GDTO3";
        else $axiom_group=$object["customergroup"];
        $customergroup=$repositoryCustomerGroups->findOneBy(["name"=>$axiom_group]);
        $bankaccounts=$repositoryBankAccounts->findOneBy(["iban"=>$object["iban"]]);

        $id_paymentterms_nav=$object["payment_terms"];
        $paymentTerms=null;
              //30 DÍAS
        if( $id_paymentterms_nav=="001" OR $id_paymentterms_nav=="011" OR $id_paymentterms_nav=="018" OR $id_paymentterms_nav=="021"
        OR $id_paymentterms_nav=="026" OR $id_paymentterms_nav=="027" OR $id_paymentterms_nav=="047" OR $id_paymentterms_nav=="064"
        OR $id_paymentterms_nav=="071" OR $id_paymentterms_nav=="072" OR $id_paymentterms_nav=="075" OR $id_paymentterms_nav=="094")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"95"]);
        }
        //60 DÍAS
        else if( $id_paymentterms_nav=="002" OR $id_paymentterms_nav=="012" OR $id_paymentterms_nav=="022" OR $id_paymentterms_nav=="027"
        OR $id_paymentterms_nav=="036" OR $id_paymentterms_nav=="049" OR $id_paymentterms_nav=="082")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"96"]);
        }
        //75 DÍAS
        else if( $id_paymentterms_nav=="003" OR $id_paymentterms_nav=="010" OR $id_paymentterms_nav=="013" OR $id_paymentterms_nav=="020"
        OR $id_paymentterms_nav=="030" OR $id_paymentterms_nav=="033" OR $id_paymentterms_nav=="034" OR $id_paymentterms_nav=="035"
        OR $id_paymentterms_nav=="048" OR $id_paymentterms_nav=="053" OR $id_paymentterms_nav=="057" OR $id_paymentterms_nav=="060"
        OR $id_paymentterms_nav=="062" OR $id_paymentterms_nav=="073" OR $id_paymentterms_nav=="081" OR $id_paymentterms_nav=="082")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"97"]);
        }
        //30-60 DÍAS
        else if( $id_paymentterms_nav=="004" OR $id_paymentterms_nav=="014" OR $id_paymentterms_nav=="054")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"102"]);
        }
        //30-60-90 DÍAS
        else if( $id_paymentterms_nav=="005")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"103"]);
        }
        //30-60-75 DÍAS
        else if($id_paymentterms_nav=="006" OR $id_paymentterms_nav=="015" OR $id_paymentterms_nav=="041" OR $id_paymentterms_nav=="052")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"104"]);
        }
        //15 DÍAS
        else if($id_paymentterms_nav=="007" OR $id_paymentterms_nav=="031" OR $id_paymentterms_nav=="042")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"92"]);
        }
        //45 DÍAS
        else if($id_paymentterms_nav=="008" OR $id_paymentterms_nav=="085")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"98"]);
        }
        //23 DÍAS
        else if($id_paymentterms_nav=="016")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"94"]);
        }
        //5 DÍAS
        else if($id_paymentterms_nav=="017")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"89"]);
        }
        //60-75 DÍAS
        else if($id_paymentterms_nav=="019" OR $id_paymentterms_nav=="023" OR $id_paymentterms_nav=="037" OR $id_paymentterms_nav=="040")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"105"]);
        }
        //20 DÍAS
        else if($id_paymentterms_nav=="043" OR $id_paymentterms_nav=="045")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"93"]);
        }
        //8 DÍAS
        else if($id_paymentterms_nav=="044")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"91"]);
        }
        //30-60-85 DÍAS
        else if($id_paymentterms_nav=="050")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"106"]);
        }
        //60-90 DÍAS
        else if($id_paymentterms_nav=="063")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"107"]);
        }
        //90 DÍAS
        else if($id_paymentterms_nav=="077" OR $id_paymentterms_nav=="080" OR $id_paymentterms_nav=="084" OR $id_paymentterms_nav=="086")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"99"]);
        }
        //7 DÍAS
        else if($id_paymentterms_nav=="078")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"90"]);
        }
        //30-45-60 DÍAS
        else if($id_paymentterms_nav=="079")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"108"]);
        }
        //60-90-120 DÍAS
        else if($id_paymentterms_nav=="083")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"109"]);
        }
        //180 DÍAS
        else if($id_paymentterms_nav=="093")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"101"]);
        }
        //30-60-90-120 DÍAS
        else if($id_paymentterms_nav=="095")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"110"]);
        }
        //90-120-150 DÍAS
        else if($id_paymentterms_nav=="096")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"111"]);
        }
        //90-120 DÍAS
        else if($id_paymentterms_nav=="097")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"112"]);
        }
        //120 DÍAS
        else if($id_paymentterms_nav=="098")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"100"]);
        }
        //6 GIROS DE 30 A 180 DIAS
        else if($id_paymentterms_nav=="099")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"113"]);
        }

        if($object["socialname"]!=null)
          if($object["socialname"][0]=='*') $obj->setActive(0);
            else $obj->setActive(1);
        else $obj->setActive(1);
        $obj->setVat($object["vat"]);
        $obj->setName(ltrim(ltrim($object["name"]),'*'));
        $obj->setSocialname(ltrim(ltrim($object["socialname"]),'*'));
        $obj->setAddress(rtrim($object["address1"]." ".$object["address2"]));
        $obj->setCity($object["city"]);
        $obj->setPostcode($object["postcode"]);
        $obj->setPhone(ltrim($object["phone"]));
        $obj->setWeb($object["web"]);
        $obj->setEmail($object["email"]);
        $obj->setCountry($country);
        $obj->setState($state);
        $obj->setDateupd(new \Datetime());
        $obj->setPaymentMethod($paymentMethod);
        if($paymentTerms!=NULL) $obj->setPaymentTerms($paymentTerms);
        $obj->setActivity($activity);
        $obj->setMinimuminvoiceamount($object["minimuminvoiceamount"]);
        $obj->setMaxcredit($object["creditlimit"]);
        $obj->setPaymentMode($object["paymentmode"]);




         $customer=$repository->findOneBy(["code"=>$object["code"]]);
         if($customer!=NULL){
            /*DATOS PARA PEDIDOS*/
             $ordersData=$repositoryCustomerOrdersData->findOneBy(["customer"=>$customer]);

             if($ordersData==NULL)
             {
               $ordersData=new ERPCustomerOrdersData();
               $ordersData->setCustomer($customer);
               $ordersData->setDateadd(new \Datetime());
               $ordersData->setDateupd(new \Datetime());
               $ordersData->setDeleted(0);
               $ordersData->setActive(1);

             }
             $carrier=$repositoryCarriers->findOneBy(["code"=>$object["carrier"]]);
             $shippingconditions=$repositoryCarrierShippingConditions->findOneBy(["code"=>$object["shippingconditions"]]);

             $ordersData->setRequiredordernumber($object["requiredordernumber"]);
             $ordersData->setInvoicefordeliverynote($object["invoicefordeliverynote"]);
             $ordersData->setPricesdeliverynote($object["pricesdeliverynote"]);
             $ordersData->setPartialshipping($object["partialshipping"]);
             $ordersData->setAuthorizationcontrol($object["authorizationcontrol"]);
             $ordersData->setCarrier($carrier);
             $ordersData->setShippingconditions($shippingconditions);
             $this->doctrine->getManager()->persist($ordersData);

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

             $commercialterms->setAllowlinediscount($object["allowlinediscount"]);
             $this->doctrine->getManager()->persist($commercialterms);
        }

        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();

        //el cliente tiene datos bancarios
        if($object["iban"]!=NULL)
        {
            $objbankaccount=$repositoryBankAccounts->findOneBy(["iban"=>$object["iban"]]);
            if($objbankaccount==NULL){
              $customer=$repository->findOneBy(["code"=>$object["code"]]);
              $objbankaccount=new ERPBankAccounts();
              $objbankaccount->setCustomer($customer);
              $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
              $company=$repositoryCompanies->find(2);
              $objbankaccount->setCompany($company);
              $objbankaccount->setDateadd(new \Datetime());
              $objbankaccount->setDeleted(0);
              $objbankaccount->setActive(1);

            }

            $objbankaccount->setIban($object["iban"]);
           if(strlen($object["swift"])=="11" OR strlen($object["swift"])=="8") $objbankaccount->setSwiftcode($object["swift"]);
            else{
              $objbankaccount->setSwiftcode("REVISAR");
              $txt="El cliente ".$object["socialname"]." tiene un SWIFT no válido =>".$object["swift"] . "\n";
              fwrite($log, $txt);
            }
            $objbankaccount->setDateupd(new \Datetime());
            $this->doctrine->getManager()->persist($objbankaccount);
            $this->doctrine->getManager()->flush();
            $this->doctrine->getManager()->clear();
        }

     }
     fclose($log);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customers"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("customers");
     }

     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
     $this->doctrine->getManager()->persist($navisionSync);
     $this->doctrine->getManager()->flush();
     //------   Critical Section END   ------
     //------   Remove Lock Mutex    ------
     fclose($fp);
   }



   public function importCustomerComment(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     $fp = fopen('/tmp/axiom-navisionGetCustomers-importCustomerComment.lock', 'c');
     //$fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetCustomers-importCustomerComment.lock', 'c');
     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de comentarios de clientes: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customercomments"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando comentarios de clientes....');
     $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getComments.php?type=1&from='.$navisionSync->getMaxtimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
     $repository=$this->doctrine->getRepository(ERPCustomerCommentLines::class);

     //Disable SQL logger
     $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

     foreach ($objects["class"] as $key=>$object){
       $customer=$repositoryCustomers->findOneBy(["code"=>$object["entity"]]);
       if($object["comment"]!="" AND $customer!=NULL)
       {

         //comentarios generales del cliente (type=0)
         if($object["code"]=="")
         {
           $output->writeln('  - '.$object["entity"]);
           $obj=$repository->findOneBy(["comment"=>$object["comment"]]);
           if ($obj==null) {
             $obj=new ERPCustomerCommentLines();
             $obj->setComment($object["comment"]);
             $obj->setType(0);
             $datetime=new \DateTime(date('Y-m-d 00:00:00',strtotime($object["date"]["date"])));
             $obj->setDateadd($datetime);
             $obj->setCustomer($customer);
             $obj->setDeleted(0);
             $obj->setActive(1);
             $obj->setDateupd($datetime);
             $this->doctrine->getManager()->persist($obj);
             $this->doctrine->getManager()->flush();
             $this->doctrine->getManager()->clear();
           }
       }

       else if($object["code"]=="VENTAS")
       {
         $output->writeln('  - '.$object["entity"].' - VENTAS');
         $obj=$repository->findOneBy(["comment"=>$object["comment"]]);
         if ($obj==null) {
           $obj=new ERPCustomerCommentLines();
           $obj->setComment($object["comment"]);
           $obj->setType(1);
           $datetime=new \DateTime(date('Y-m-d 00:00:00',strtotime($object["date"]["date"])));
           $obj->setDateadd($datetime);

           $obj->setCustomer($customer);
           $obj->setDeleted(0);
           $obj->setActive(1);
           $obj->setDateupd($datetime);
           $this->doctrine->getManager()->persist($obj);
           $this->doctrine->getManager()->flush();
           $this->doctrine->getManager()->clear();
         }
       }
     }

     }
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customercomments"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("customercomments");
     }
     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
     $this->doctrine->getManager()->persist($navisionSync);
     $this->doctrine->getManager()->flush();
     //------   Critical Section END   ------
     //------   Remove Lock Mutex    ------
     fclose($fp);
   }


   public function importCustomerContact(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     $fp = fopen('/tmp/axiom-navisionGetCustomers-importCustomerContact.lock', 'c');
    // $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetCustomers-importCustomerContact.lock', 'c');
     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de contactos de clientes: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
     $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
     $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
     $repositoryDepartments=$this->doctrine->getRepository(ERPDepartments::class);
     $repository=$this->doctrine->getRepository(ERPContacts::class);
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customercontacts"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando contactos de los clientes....');


       //Disable SQL logger
       $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
       $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getAllContacts.php?type=0&from='.$navisionSync->getMaxtimestamp());
       $objects=json_decode($json, true);
       $objects=$objects[0];
       foreach ($objects["class"] as $key=>$object){
         $output->writeln("Vamos a analizar el contacto ".$object["code"]." correspondiente al cliente ".$object["customer"]);
         $obj=$repository->findOneBy(["code"=>$object["code"]]);
         if ($obj==null) {

           $obj=new ERPContacts();
           $obj->setCode($object["code"]);
           $obj->setDateadd(new \Datetime());
           $obj->setDeleted(0);
           $obj->setActive(1);
         }
          $country=$repositoryCountries->findOneBy(["alfa2"=>$object["country"]]);
          $state=$repositoryStates->findOneBy(["name"=>$object["state"]]);
          $customer=$repositoryCustomers->findOneBy(["code"=>$object["customer"]]);
          $department=$repositoryDepartments->findOneBy(["code"=>$object["department"]]);
          $obj->setName(ltrim(ltrim($object["name"]),'*'));
          $obj->setAddress(rtrim($object["address1"]." ".$object["address2"]));
          $obj->setCity($object["city"]);
          $obj->setPostcode($object["postcode"]);
          $obj->setPhone(ltrim($object["phone"]));
          $obj->setEmail($object["email"]);
          $obj->setCountry($country);
          $obj->setState($state);
          $obj->setCustomer($customer);
          $obj->setDepartment($department);
          $obj->setPosition($object["jobtitle"]);
          $obj->setDateupd(new \Datetime());

          $this->doctrine->getManager()->persist($obj);
          $this->doctrine->getManager()->flush();
       }

  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customercontacts"]);
   if ($navisionSync==null) {
     $navisionSync=new NavisionSync();
     $navisionSync->setEntity("customercontacts");
   }
   $navisionSync->setLastsync($datetime);
   $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
   $this->doctrine->getManager()->persist($navisionSync);
   $this->doctrine->getManager()->flush();
   //------   Critical Section END   ------
   //------   Remove Lock Mutex    ------
   fclose($fp);
 }

 public function importCustomerAddresses(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     $fp = fopen('/tmp/axiom-navisionGetCustomers-importCustomerAddresses.lock', 'c');
    //$fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetCustomers-importCustomerAddresses.lock', 'c');

     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de direcciones de clientes: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
     $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
     $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
     $repository=$this->doctrine->getRepository(ERPAddresses::class);
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customeraddresses"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando direcciones de los clientes....');

     //Disable SQL logger
     $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
     $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getAllCustomerAddresses.php?from='.$navisionSync->getMaxtimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     foreach ($objects["class"] as $key=>$object){

       $output->writeln("Vamos a analizar la dirección ".$object["code"]." del cliente ".$object["customer"]);
       $obj=$repository->findOneBy(["code"=>$object["code"]]);
       if ($obj==null) {

         $obj=new ERPAddresses();
         $obj->setCode($object["code"]);
         $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
         $company=$repositoryCompanies->find(2);
         $obj->setCompany($company);
         $obj->setDateadd(new \Datetime());
         $obj->setDeleted(0);
         $obj->setActive(1);
       }

      if($object["country"]==NULL) $country=$repositoryCountries->findOneBy(["alfa2"=>"ES"]);
        else $country=$repositoryCountries->findOneBy(["alfa2"=>$object["country"]]);


        $state=$repositoryStates->findOneBy(["name"=>$object["state"]]);
        $customer=$repositoryCustomers->findOneBy(["code"=>$object["customer"]]);
        $obj->setName(ltrim(ltrim($object["name"]),'*'));
        $obj->setAddress(rtrim($object["address1"]." ".$object["address2"]));
        $obj->setCity($object["city"]);
        $obj->setPostcode($object["postcode"]);
        $obj->setPhone(ltrim($object["phone"]));
        $obj->setEmail($object["email"]);
        $obj->setCountry($country);
        $obj->setState($state);
        $obj->setCustomer($customer);
        $obj->setNavisioncontact($object["navisioncontact"]);
        $obj->setInvoiceaddress($object["invoiceaddress"]);
        $obj->setDeliveryaddress($object["deliveryaddress"]);
        $obj->setDateupd(new \Datetime());

        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();
      }


   $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customeraddresses"]);
   if ($navisionSync==null) {
     $navisionSync=new NavisionSync();
     $navisionSync->setEntity("customeraddresses");
   }
   $navisionSync->setLastsync($datetime);
   $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
   $this->doctrine->getManager()->persist($navisionSync);
   $this->doctrine->getManager()->flush();
   //------   Critical Section END   ------
   //------   Remove Lock Mutex    ------
   fclose($fp);
}

}
?>
