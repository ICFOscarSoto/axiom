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
use App\Modules\ERP\Entity\ERPShoppingDiscounts;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStoreLocations;
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
      case 'clearEAN13': $this->clearEAN13($input, $output);
      break;
      case 'prices': $this->importPrices($input, $output);
      break;
      case 'stocks': $this->importStocks($input, $output);
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
      }$navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"products"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setEntity("products");
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
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getEAN13.php?from='.$navisionSync->getMaxtimestamp());
      $objects=json_decode($json, true);
      $objects=$objects[0];

      $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
      $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
      $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
      $repository=$this->doctrine->getRepository(ERPEAN13::class);
      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
      $log=fopen("logEAN13.txt", "w");
      foreach ($objects["class"] as $key=>$object){
        $output->writeln('  - '.$object["Item No."].' - '.$object["Cross-Reference No."]);
        //$obj=$repository->findOneBy(["name"=>$object["Cross-Reference No."]]);
        $nameEAN13=preg_replace('/\D/','',$object["Cross-Reference No."]);
        if (strlen($nameEAN13)==13) {
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

        } else {
          $txt;
          if ($obj==null) {
            $txt="Este EAN13 no es válido ".$object["Cross-Reference No."] . "\n";
          } else {
            $txt="Este EAN13 está duplicado ".$object["Cross-Reference No."] . "\n";
          }
          fwrite($log, $txt);
        }

        $this->doctrine->getManager()->clear();

      }
      fclose($log);
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
/*
  Busco los EAN13 de axiom en Navision, y si no están los desactivo
 */
public function clearEAN13(InputInterface $input, OutputInterface $output){
        $repository=$this->doctrine->getRepository(ERPEAN13::class);
        $datetime=new \DateTime();
        $output->writeln('* Limpiando EAN13....');
        $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getEAN13.php');
        $objects=json_decode($json, true);
        $objects=$objects[0];
        //Disable SQL logger
        $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
        $oldEAN13s=$repository->findAll();
        foreach ($oldEAN13s as $oldEAN13){
            $count=0;
            $EAN13=$oldEAN13->getName();
            foreach ($objects["ean13"] as $key=>$object){
                $nameEAN13=preg_replace('/\D/','',$object["Cross-Reference No."]);
                if ($EAN13==$nameEAN13) {
                  $count=1;
                  break;
                }
            }
            if ($count==0) {
              $oldEAN13->setDeleted(1);
              $oldEAN13->setActive(0);
              $this->doctrine->getManager()->flush();
              $this->doctrine->getManager()->clear();
            }
        }
}


/*
  Si el producto no tiene descuento de compra, busco en Navision (Purchase Line Discount) los descuentos asociados que tiene.
  Entonces los devuelvo y se los asigno al proveedor y la categoría del producto.
 */

public function importPrices(InputInterface $input, OutputInterface $output) {
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prices"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Sincronizando precios....');
  $repositoryCategory=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryShoppingDiscounts=$this->doctrine->getRepository(ERPShoppingDiscounts::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $products=$repository->findAll();

  //Disable SQL logger
  foreach($products as $product) {
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
    $price=$repositoryShoppingDiscounts->findOneBy(["supplier"=>$product->getSupplier(),"category"=>$product->getCategory()]);

    if ($price==null and $product->getCategory()!=null and $product->getSupplier()!=null){
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPrices.php?from='.$product->getCode());
      $objects=json_decode($json, true);
      $objects=$objects[0];
      foreach ($objects["class"] as $prices){
        if($prices["Discount"]!=0){
          $supplier=$repositorySupliers->find($product->getSupplier()->getId());
          $categoy=$repositoryCategory->find($product->getCategory()->getId());
          $obj=new ERPShoppingDiscounts();
          $obj->setSupplier($supplier);
          $obj->setCategory($categoy);
          $obj->setDiscount($prices["Discount"]);
          $obj->setDiscount1($prices["Discount1"]);
          $obj->setDiscount2($prices["Discount2"]);
          $obj->setDiscount3($prices["Discount3"]);
          $obj->setDiscount4($prices["Discount4"]);
          $obj->setQuantity($prices["Quantity"]);
          $obj->setStart(date_create_from_format("Y-m-d h:i:s.u",$prices["Starting"]["date"]));
          if ($prices["Ending"]["date"]=="1753-01-01 00:00:00.000000") {
            $obj->setEnd(null);
          } else $obj->setEnd(date_create_from_format("Y-m-d h:i:s.u",$prices["Ending"]["date"]));
          $obj->setDateadd(new \Datetime());
          $obj->setDateupd(new \Datetime());
          if (strtotime($prices["Ending"]["date"])<strtotime(date("d-m-Y H:i:00",time())) && $prices["Ending"]["date"]!="1753-01-01 00:00:00.000000" ) {
            $obj->setActive(0);
          } else $obj->setActive(1);
          $obj->setDeleted(0);
          $this->doctrine->getManager()->merge($obj);
          $this->doctrine->getManager()->flush();
          $this->doctrine->getManager()->clear();
        }
      }
    }
  }
}


public function importStocks(InputInterface $input, OutputInterface $output) {
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"stocks"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Sincronizando stocks....');
  $repositoryStocks=$this->doctrine->getRepository(ERPStocks::class);
  $repositoryStoreLocations=$this->doctrine->getRepository(ERPStoreLocations::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $products=$repository->findAll();
  $product=$repository->find(306);
//  foreach ($products as $product){
    $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getStocks.php?from='.$product->getCode());
    $objects=json_decode($json, true);
    $objects=$objects[0];
    if ($objects){
    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $company=$repositoryCompanies->find(2);
    $output->writeln('* Actualizando stocks '.$product->getCode());
    foreach ($objects["class"] as $stock){
      $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["Location Code"]]);
      if($location!=null){
      $stock_old=$repositoryStocks->findOneBy(["product"=>$product->getId(),"storelocation"=>$location->getId()]);

      if($stock_old!=null){
        $stock_old->setQuantity((int)$stock["stock"]);
        $stock_old->setDateupd(new \Datetime());
        $this->doctrine->getManager()->merge($stock_old);
      } else {
        $obj=new ERPStocks();
        $obj->setCompany($company);
        $obj->setProduct($product);
        $obj->setDateadd(new \Datetime());
        $obj->setDateupd(new \Datetime());
        $obj->setStoreLocation($location);
        if ((int)$stock["stock"]<0) $quantiy=0; else $quantity=(int)$stock["stock"];
        $obj->setQuantity($quantity);
        $obj->setActive(1);
        $obj->setDeleted(0);
        $this->doctrine->getManager()->merge($obj);
      }

    }


    }
  }
    $this->doctrine->getManager()->flush();
    $this->doctrine->getManager()->clear();
//  }

}

}
?>
