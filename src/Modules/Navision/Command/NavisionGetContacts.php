<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPContacts;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPDepartments;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetContacts extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getcontacts')
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
  // $this->company=$repositoryCompanies->find(2);

    $output->writeln('');
    $output->writeln('Comenzando sincronizacion Navision');
    $output->writeln('==================================');
    switch($entity){
      case 'contacts': $this->importContact($input, $output);
      break;
      case 'all':
        $this->importContact($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importContact(InputInterface $input, OutputInterface $output){
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"contacts"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando contactos....');
     $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getContacts.php?from='.$navisionSync->getMaxtimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     //dump($products["products"]);
      $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
     //$repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);
     //$repositoryPaymentMethod=$this->doctrine->getRepository(ERPPaymentMethods::class);
    // $repositoryPaymentTerms=$this->doctrine->getRepository(ERPPaymentTerms::class);
     $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
     $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
     $repositoryDepartments=$this->doctrine->getRepository(ERPDepartments::class);
     $repository=$this->doctrine->getRepository(ERPContacts::class);

     //Disable SQL logger
     $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

     foreach ($objects["class"] as $key=>$object){
       $output->writeln('  - '.$object["code"].' - '.$object["name"]);
       $obj=$repository->findOneBy(["code"=>$object["code"]]);
       if ($obj==null) {
         $obj=new ERPContacts();
         $obj->setCode($object["code"]);
         $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
         //$company=$repositoryCompanies->find(2);
         //$obj->setCompany($company);
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
        if($object["authorizationcontrol"]) $obj->setAuthorizationcontrol(1);
        $obj->setDateupd(new \Datetime());
        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();

     }
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"contacts"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("contacts");
     }
     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
     $this->doctrine->getManager()->persist($navisionSync);
     $this->doctrine->getManager()->flush();
   }


}
?>
