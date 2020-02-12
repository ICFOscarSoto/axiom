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
        $customergroup=$repositoryCustomerGroups->findOneBy(["code"=>$object["customergroup"]]);
        $bankaccounts=$repositoryBankAccounts->findOneBy(["iban"=>$object["iban"]]);
        if($object["payment_terms"]!="") $paymentTerms=$repositoryPaymentTerms->findOneBy(["code"=>$object["payment_terms"]]);
        else $paymentTerms=$repositoryPaymentTerms->findOneBy(["id"=>"11"]);
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
        $obj->setPaymentTerms($paymentTerms);
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
            $objbankaccount->setSwiftcode($object["swift"]);
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
