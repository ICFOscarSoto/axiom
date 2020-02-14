<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPPaymentTerms;
use App\Modules\ERP\Entity\ERPCustomerActivities;
use App\Modules\ERP\Entity\ERPCustomerGroups;
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
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importCustomer(InputInterface $input, OutputInterface $output){
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
     $repositoryCustomerActivities=$this->doctrine->getRepository(ERPCustomerActivities::class);
     $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
     $repositoryCustomerGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
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
        $customergroup=$repositoryCustomerGroups->findOneBy(["name"=>$object["customergroup"]]);
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
        else if($id_paymentterms_nav=="089")
        {
          $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"94"]);
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
        //$obj->setCurrency($currency);
        $obj->setPaymentMethod($paymentMethod);
        if($paymentTerms!=NULL) $obj->setPaymentTerms($paymentTerms);
        $obj->setActivity($activity);
        $obj->setCustomergroup($customergroup);
        $obj->setMinimuminvoiceamount($object["minimuminvoiceamount"]);
        $obj->setMaxcredit($object["creditlimit"]);
        $obj->setAuthorizationControl($object["authorizationcontrol"]);

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
            if(strlen($object["swift"])=="11") $objbankaccount->setSwiftcode($object["swift"]);
            else $txt="El cliente ".$object["socialname"]." tiene un SWIFT no válido =>".$object["swift"] . "\n"
              fwrite($log, $txt);
            }
            $objbankaccount->setDateupd(new \Datetime());
            $this->doctrine->getManager()->persist($objbankaccount);
            $this->doctrine->getManager()->flush();
            $this->doctrine->getManager()->clear();
        }

     }
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customers"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("customers");
     }
     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
     $this->doctrine->getManager()->persist($navisionSync);
     $this->doctrine->getManager()->flush();
   }


}
?>
