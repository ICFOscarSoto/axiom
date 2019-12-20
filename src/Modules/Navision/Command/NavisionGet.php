<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGet extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:get')
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
       $navisionSync->setEntity("customers");
       $navisionSync->setLastsync(new \DateTime("@0"));
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando clientes....');
     $json=file_get_contents($this->url.'navisionExport/do-NAVISION-getCustomers.php?from='.$navisionSync->getLastsync()->getTimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     //dump($products["products"]);
     $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
     $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);
     $repositoryPaymentMethod=$this->doctrine->getRepository(ERPPaymentMethods::class);
     $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
     $repository=$this->doctrine->getRepository(ERPCustomers::class);
     foreach ($objects["class"] as $key=>$object){
       $output->writeln('  - '.$object["code"].' - '.$object["socialname"]);
       if($object["vat"]==null) continue;
       $obj=$repository->findOneBy(["code"=>$object["code"]]);
       if ($obj==null) {
         $obj=new ERPCustomers();
         $obj->setCode($object["code"]);
         $obj->setCompany($this->company);
         $obj->setDateadd(new \Datetime());
         $obj->setDateupd(new \Datetime());
         $obj->setDeleted(0);
         $obj->setActive(1);
       }
        $country=$repositoryCountries->findOneBy(["alfa2"=>$object["country"]]);
        $state=$repositoryStates->findOneBy(["name"=>$object["state"]]);
        $currency=$repositoryCurrencies->findOneBy(["isocode"=>"EUR"]);
        $paymentMethod=$repositoryPaymentMethod->findOneBy(["id"=>1]);
        $obj->setVat($object["vat"]);
        $obj->setName($object["name"]);
        $obj->setSocialname($object["socialname"]);
        $obj->setAddress(rtrim($object["address1"]." ".$object["address2"]));
        $obj->setCity($object["city"]);
        $obj->setPostcode($object["postcode"]);
        $obj->setPhone($object["phone"]);
        $obj->setWeb($object["web"]);
        $obj->setEmail($object["email"]);
        $obj->setCountry($country);
        $obj->setState($state);
        //$obj->setCurrency($currency);
        $obj->setPaymentMethod($paymentMethod);
        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();
     }
     $navisionSync->setLastsync($datetime);
     $this->doctrine->getManager()->persist($navisionSync);
     $this->doctrine->getManager()->flush();
     return new Response(null);
   }

}
?>
