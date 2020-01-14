<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetProducts extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getproducts')
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
      case 'products': $this->importProduct($input, $output);
      break;
      case 'ean13': $this->importEAN13($input, $output);
      break;
      case 'all':
        $this->importProduct($input, $output);
        $this->importEAN13($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

    public function importProduct(InputInterface $input, OutputInterface $output){
      $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"products"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setMaxtimestamp(0);
      }
      $datetime=new \DateTime();
      $output->writeln('* Sincronizando productos....');
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProducts.php?from='.$navisionSync->getMaxtimestamp());
      $objects=json_decode($json, true);
      $objects=$objects[0];
      //dump($products["products"]);
      $repositoryCategory=$this->doctrine->getRepository(ERPCategories::class);
      $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
      $repository=$this->doctrine->getRepository(ERPProducts::class);

      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

      foreach ($objects["class"] as $key=>$object){
        $output->writeln('  - '.$object["code"].' - '.$object["Description"]);
        //if($object["vat"]==null) continue;
        $obj=$repository->findOneBy(["code"=>$object["code"]]);
        $oldobj=$obj;
        if ($obj==null) {
          $obj=new ERPProducts();
          $obj->setCode($object["code"]);
          $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
          $company=$repositoryCompanies->find(2);
          $obj->setCompany($company);
          $obj->setDateadd(new \Datetime());
          $obj->setDateupd(new \Datetime());
          $obj->setDeleted(0);
          $obj->setActive(1);
          $category=$repositoryCategory->findOneBy(["name"=>"Sin Categoria"]);
          $obj->setCategory($category);
        }
          $supplier=$repositorySupliers->findOneBy(["code"=>$object["Supplier"]]);
         if($object["Blocked"]==0) $obj->setActive(1); else $obj->setActive(0);
         $obj->setCode($object["code"]);
         $obj->setName($object["Description"]);
         $obj->setWeight($object["Weight"]);
         $obj->setPVPR($object["ShoppingPrice"]);
         $obj->setSupplier($supplier);
         //$obj->preProccess($this, $this->doctrine, null, null, $oldobj);
         $this->doctrine->getManager()->merge($obj);
         $this->doctrine->getManager()->flush();
         $this->doctrine->getManager()->clear();
      }$navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"customers"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setEntity("customers");
      }
      $navisionSync->setLastsync($datetime);
      $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
      $this->doctrine->getManager()->persist($navisionSync);
      $this->doctrine->getManager()->flush();
    }

    public function importEAN13(InputInterface $input, OutputInterface $output){
      $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"EAN13"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setMaxtimestamp(0);
      }
      $datetime=new \DateTime();
      $output->writeln('* Sincronizando EAN13....');
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getReferences.php?from='.$navisionSync->getMaxtimestamp());
      $objects=json_decode($json, true);
      $objects=$objects[0];
      $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
      $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
      $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
      $repository=$this->doctrine->getRepository(ERPEAN13::class);

      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

      foreach ($objects["class"] as $key=>$object){
        $output->writeln('  - '.$object["Item No."].' - '.$object["Cross-Reference No."]);
        $obj=$repository->findOneBy(["name"=>$object["Cross-Reference No."]]);
        $nameEAN13=preg_replace('/\D/','',$object["Cross-Reference No."]);
        if ($obj==null and strlen($nameEAN13)==13) {
          $obj=new ERPEAN13();
          $obj->setName($nameEAN13);
          $obj->setDateadd(new \Datetime());
          $obj->setDateupd(new \Datetime());
          $obj->setDeleted(0);
          $obj->setActive(1);
          $customer=$repositoryCustomers->findOneBy(["code"=>$object["Cross-Reference Type No."]]);
          if ($customer==null){
            $supplier=$repositorySupliers->findOneBy(["code"=>$object["Cross-Reference Type No."]]);
            $obj->setSupplier($supplier);
          } else $obj->setCustomer($customer);

          $product=$repositoryProducts->findOneBy(["code"=>$object["Item No."]]);
          if ($product!=null) {
          $obj->setProduct($product);
          $this->doctrine->getManager()->merge($obj);
          $this->doctrine->getManager()->flush();
        }
          $this->doctrine->getManager()->clear();
        }


      }
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"EAN13"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setEntity("EAN13");
      }
      $navisionSync->setLastsync($datetime);
      $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
      $this->doctrine->getManager()->persist($navisionSync);
      $this->doctrine->getManager()->flush();
    }

}
?>
