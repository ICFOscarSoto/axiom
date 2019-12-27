<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPProducts;
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
      case 'all':
        $this->importProduct($input, $output);
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
        $navisionSync->setEntity("products");
        $navisionSync->setLastsync(new \DateTime("@0"));
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
          $obj->setCompany($this->company);
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
         /*$obj->setCity($object["city"]);
         $obj->setPostcode($object["postcode"]);
         $obj->setPhone($object["phone"]);
         $obj->setWeb($object["web"]);
         $obj->setEmail($object["email"]);
         $obj->setCountry($country);
         $obj->setState($state);
         $obj->setCurrency($currency);
         $obj->setPaymentMethod($paymentMethod);*/
         //$obj->preProccess($this, $this->doctrine, null, null, $oldobj);
         $this->doctrine->getManager()->merge($obj);
         $this->doctrine->getManager()->flush();
         $this->doctrine->getManager()->clear();
      }
      $navisionSync->setLastsync($datetime);
      $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
      $this->doctrine->getManager()->persist($navisionSync);
      $this->doctrine->getManager()->flush();
    }

}
?>
