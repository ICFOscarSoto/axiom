<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductPrices;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPShoppingDiscounts;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPIncrements;
use App\Modules\ERP\Entity\ERPOfferPrices;
use App\Modules\ERP\Entity\ERPCustomerIncrements;
use App\Modules\ERP\Entity\ERPCustomerPrices;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPVariantsValues;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleTaxes;
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
      case 'increments': $this->importIncrements($input, $output);
      break;
      case 'offers': $this->importOffers($input, $output);
      break;
      case 'variants': $this->importVariants($input, $output);
      break;
      case 'values': $this->importProductsVariants($input, $output);
      break;
      case 'all':
        $this->importProduct($input, $output);
        $this->clearEAN13($input, $output);
        $this->importEAN13($input, $output);
        $this->importPrices($input, $output);
        $this->importStocks($input, $output);
        $this->importIncrements($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }
  }

    public function importProduct(InputInterface $input, OutputInterface $output){
      //------   Create Lock Mutex    ------
      $fp = fopen('/tmp/axiom-navisionGetProducts-importProduct.lock', 'c');
      //$fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importProduct.lock', 'c');
      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la sincronizacion de productos: El proceso ya esta en ejecución.');
        exit;
      }

      //------   Critical Section START   ------
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
         // Comprobamos si el producto no tiene movimientos desde 2017, en caso de que no tenga lo desactivamos
         $json2=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-clearProducts.php?from='.$object["code"]);
         $movs=json_decode($json2, true);
         $movs=$movs[0];
         if($movs["class"][0]["movimiento"]!=null)
          if($movs["class"][0]["movimiento"]["date"]>"2018-01-01 00:00:00.000000" and $object["Blocked"]==0)
            $obj->setActive(1);
            else $obj->setActive(0);
         else $obj->setActive(0);
         $repositoryTaxes=$this->doctrine->getRepository(GlobaleTaxes::class);
         $taxes=$repositoryTaxes->find(1);
         $obj->setTaxes($taxes);
         $obj->setCode($object["code"]);
         $obj->setName($object["Description"]);
         $obj->setWeight($object["Weight"]);
         // Comprobamos si el producto tiene descuentos, si no los tiene se le pone como precio neto.
         $json3=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPrices.php?from='.$object["code"].'$supplier='.$object["Supplier"]);
         $prices=json_decode($json3, true);
         $prices=$prices[0];
         $obj->setnetprice(1);
         foreach ($prices["class"] as $price){
           if($price["Discount"]!=0){
             if ($prices["Ending"]["date"]=="1753-01-01 00:00:00.000000") {
               $obj->setnetprice(0);
             }
           }
         }
         if (!$obj->getnetprice()){
           $obj->setPVPR($object["ShoppingPrice"]);
           $obj->setShoppingPrice(0);
         } else {
           $obj->setPVPR(0);
           $obj->setShoppingPrice($object["ShoppingPrice"]);
         }
         $obj->setSupplier($supplier);
         $this->doctrine->getManager()->merge($obj);
         $this->doctrine->getManager()->flush();
         $obj->priceCalculated($this->doctrine);
         $this->doctrine->getManager()->clear();
      }$navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"products"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setEntity("products");
      }
      $navisionSync->setLastsync($datetime);
      $output->writeln('* El nuevo maxtimestamp es ....'.$objects["maxtimestamp"]);
      if ($objects["maxtimestamp"]>$navisionSync->getMaxtimestamp())
      $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
      $this->doctrine->getManager()->persist($navisionSync);
      $this->doctrine->getManager()->flush();
      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
    }

    public function importEAN13(InputInterface $input, OutputInterface $output){
      //------   Create Lock Mutex    ------
      $fp = fopen('/tmp/axiom-navisionGetProducts-importEAN13.lock', 'c');
      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la sincronizacion de EAN13: El proceso ya esta en ejecución.');
        exit;
      }

      //------   Critical Section START   ------
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
      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
    }

/*
  Busco los EAN13 de axiom en Navision, y si no están los desactivo
 */
public function clearEAN13(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  $fp = fopen('/tmp/axiom-navisionGetProducts-clearEAN13.lock', 'c');
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de limpieza de EAN13: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
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
  //------   Critical Section END   ------
  //------   Remove Lock Mutex    ------
  fclose($fp);
}

/*
  Si el producto no tiene descuento de compra, busco en Navision (Purchase Line Discount) los descuentos asociados que tiene.
  Entonces los devuelvo y se los asigno al proveedor y la categoría del producto.
 */
public function importPrices(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  $fp = fopen('/tmp/axiom-navisionGetProducts-importPrices.lock', 'c');
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de precios: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
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
    if ($price==null && $product->getCategory()!=null && $product->getSupplier()!=null){
      $supplier=$repositorySupliers->findOneBy(["id"=>$product->getSupplier()->getId()]);
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPrices.php?from='.$product->getCode().'&supplier='.$supplier->getCode());
      $objects=json_decode($json, true);
      $objects=$objects[0];
      foreach ($objects["class"] as $prices){
        if($prices["Discount"]!=0){
          $category=$repositoryCategory->findOneBy(["id"=>$product->getCategory()->getId()]);
          $output->writeln(' --> El producto '.$product->getCode().' esta anadiendo el precio '.$prices["Discount"].' al proveedor '.$supplier->getCode().' en la categoria '.$category->getName());
          $obj=new ERPShoppingDiscounts();
          $obj->setSupplier($supplier);
          $obj->setCategory($category);
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
          } else {
            $obj->setActive(1);
          }
          $obj->setDeleted(0);
          $this->doctrine->getManager()->merge($obj);
          $this->doctrine->getManager()->flush();
          $obj->setShoppingPrices($this->doctrine);
          $this->doctrine->getManager()->clear();
        }
      }
    }
  }
  //------   Critical Section END   ------
  //------   Remove Lock Mutex    ------
  fclose($fp);
}

public function importStocks(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  $fp = fopen('/tmp/axiom-navisionGetProducts-importStocks.lock', 'c');
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de stocks: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
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
  $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
    $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getStocks.php?from='.$navisionSync->getMaxtimestamp());
    $objects=json_decode($json, true);
    $objects=$objects[0];
    if ($objects){
    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $company=$repositoryCompanies->find(2);
    foreach ($objects["class"] as $stock){
      $product=$repositoryProducts->findOneBy(["code"=>$stock["code"]]);
      if ($stock["ubicacion"]!=null) {
          $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["ubicacion"]]);
      } else $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["almacen"]]);
      if ($location!=null and $product!=null) {
        $output->writeln('Actualizando stock de '.$stock["code"]. " en la localizacion ".$stock["ubicacion"]);
        $stock_old=$repositoryStocks->findOneBy(["product"=>$product->getId(),"storelocation"=>$location->getId()]);
        if($stock_old!=null){
          $stock_old->setQuantity((int)$stock["stock"]);
          $stock_old->setDateupd(new \Datetime());
          $this->doctrine->getManager()->merge($stock_old);
        }else {
          $obj=new ERPStocks();
          $obj->setCompany($company);
          $obj->setProduct($product);
          $obj->setDateadd(new \Datetime());
          $obj->setDateupd(new \Datetime());
          $obj->setStoreLocation($location);
          if ((int)$stock["stock"]<0) $quantiy=0;
          else $quantity=(int)$stock["stock"];
          $obj->setQuantity($quantity);
          $obj->setActive(1);
          $obj->setDeleted(0);
          $this->doctrine->getManager()->merge($obj);
        }
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();
      }
    }
    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"stocks"]);
    if ($navisionSync==null) {
      $navisionSync=new NavisionSync();
      $navisionSync->setEntity("stocks");
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


public function importIncrements(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  $fp = fopen('/tmp/axiom-navisionGetProducts-importIncrements.lock', 'c');
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion incrementos: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"increments"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Sincronizando incrementos....');
  $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
  $company=$repositoryCompanies->find(2);
  $repositoryCategory=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
  $repositoryCustomeGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
  $repositoryIncrements=$this->doctrine->getRepository(ERPIncrements::class);
  $repositoryCustomerIncrements=$this->doctrine->getRepository(ERPCustomerIncrements::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $repositoryproductprices=$this->doctrine->getRepository(ERPProductPrices::class);
  $repositorycustomerprices=$this->doctrine->getRepository(ERPCustomerPrices::class);
  $products=$repository->findAll();
  //Disable SQL logger
  foreach($products as $product) {
  /*  $product=$repository->findOneBy(["code"=>'208833']);*/
    $output->writeln($product->getCode().'  - '.$product->getName());
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);


    if ($product->getCategory()!=null && $product->getSupplier()!=null){
      $supplier=$repositorySupliers->findOneBy(["id"=>$product->getSupplier()->getId()]);
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getIncrements.php?product='.$product->getCode());
      $objects=json_decode($json, true);
      $objects=$objects[0];
      foreach ($objects["class"] as $increment){
        //grupos de clientes
        if($increment["type"]==1)
        {
          $customergroup=$repositoryCustomeGroups->findOneBy(["name"=>$increment["salescode"]]);

          if($customergroup!=NULL)
          {
              $incrementaxiom_ID=$repositoryIncrements->getIncrementIdByGroup($product->getSupplier(), $product->getCategory(), $customergroup);
              //no existe el incremento en axiom
              if($incrementaxiom_ID==null)
              {

                if($increment["Discount"]!=0){
                    $category=$repositoryCategory->findOneBy(["id"=>$product->getCategory()->getId()]);
                    $obj=new ERPIncrements();
                    $obj->setSupplier($supplier);
                    $obj->setCategory($category);
                    $obj->setCustomergroup($customergroup);
                    $obj->setCompany($company);
                    $pvp=$increment["pvp"];
                    $dto=$increment["Discount"];
                    $neto=$increment["neto"];
                    $precio_con_dto=$pvp-$pvp*($dto/100);
                    $inc=(($precio_con_dto/$neto)-1)*100;
                    $obj->setIncrement($inc);
                    $obj->setDateadd(new \Datetime());
                    $obj->setDateupd(new \Datetime());
                    $obj->setActive(1);
                    $obj->setDeleted(0);
                    $this->doctrine->getManager()->merge($obj);
                    $this->doctrine->getManager()->flush();
                    $obj->calculateIncrements($this->doctrine);
                }
              }
              //existe el incremento en axiom, luego hay que editarlo
              else{
                $incrementaxiom=$repositoryIncrements->findOneBy(["id"=>$incrementaxiom_ID]);
                $pvp=$increment["pvp"];
                $dto=$increment["Discount"];
                $neto=$increment["neto"];
                $precio_con_dto=$pvp-$pvp*($dto/100);
                $inc=(($precio_con_dto/$neto)-1)*100;
                $incrementaxiom->setIncrement($inc);
                $incrementaxiom->setDateupd(new \Datetime());
                $this->doctrine->getManager()->merge($incrementaxiom);
                $this->doctrine->getManager()->flush();
                $incrementaxiom->calculateIncrements($this->doctrine);

              }
            }
        }
          //cliente concreto
          else if($increment["type"]==0)
          {
            $customer=$repositoryCustomers->findOneBy(["code"=>$increment["salescode"]]);

            if($customer!=NULL)
            {
                $customerincrementaxiom_ID=$repositoryCustomerIncrements->getIncrementIdByCustomer($product->getSupplier(),$product->getCategory(),$customer);

                if($customerincrementaxiom_ID==null)
                {
                if($increment["Discount"]!=0){
                    $category=$repositoryCategory->findOneBy(["id"=>$product->getCategory()->getId()]);
                    $obj=new ERPCustomerIncrements();
                    $obj->setSupplier($supplier);
                    $obj->setCategory($category);
                    $obj->setCustomer($customer);
                    $obj->setCompany($company);
                    $pvp=$increment["pvp"];
                    $dto=$increment["Discount"];
                    $neto=$increment["neto"];
                    $precio_con_dto=$pvp-$pvp*($dto/100);
                    $inc=(($precio_con_dto/$neto)-1)*100;
                    $obj->setIncrement($inc);
                    $obj->setDateadd(new \Datetime());
                    $obj->setDateupd(new \Datetime());
                    $obj->setStart(date_create_from_format("Y-m-d h:i:s.u",$increment["startingdate"]["date"]));
                    if ($increment["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                      $obj->setEnd(null);
                    } else $obj->setEnd(date_create_from_format("Y-m-d h:i:s.u",$increment["endingdate"]["date"]));
                    $obj->setActive(1);
                    $obj->setDeleted(0);
                    $this->doctrine->getManager()->merge($obj);
                    $this->doctrine->getManager()->flush();
                    $obj->calculateIncrements($this->doctrine);
                }

              }
              else{

                $customerincrementaxiom=$repositoryCustomerIncrements->findOneBy(["id"=>$customerincrementaxiom_ID]);
                $pvp=$increment["pvp"];
                $dto=$increment["Discount"];
                $neto=$increment["neto"];
                $precio_con_dto=$pvp-$pvp*($dto/100);
                $inc=(($precio_con_dto/$neto)-1)*100;
                $customerincrementaxiom->setIncrement($inc);
                $customerincrementaxiom->setDateupd(new \Datetime());
                $customerincrementaxiom->setStart(date_create_from_format("Y-m-d h:i:s.u",$increment["startingdate"]["date"]));
                if ($increment["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                  $customerincrementaxiom->setEnd(null);
                } else $customerincrementaxiom->setEnd(date_create_from_format("Y-m-d h:i:s.u",$increment["endingdate"]["date"]));
                $this->doctrine->getManager()->merge($customerincrementaxiom);
                $this->doctrine->getManager()->flush();
                $customerincrementaxiom->calculateIncrements($this->doctrine);
              }
            }
        }

      }
        $this->doctrine->getManager()->clear();
    }
  }
  //------   Critical Section END   ------
  //------   Remove Lock Mutex    ------
  fclose($fp);
}

public function importOffers(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  $fp = fopen('/tmp/axiom-navisionGetProducts-importOffers.lock', 'c');
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de ofertas: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"offers"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Sincronizando ofertas....');
  $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
  $company=$repositoryCompanies->find(2);
  $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
  $repositoryOfferPrices=$this->doctrine->getRepository(ERPOfferPrices::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $products=$repository->findAll();
  //Disable SQL logger
 foreach($products as $product) {
   //$product=$repository->findOneBy(["code"=>'230700300680']);
    $output->writeln($product->getCode().'  - '.$product->getName());
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
    $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getOffers.php?product='.$product->getCode());
    $objects=json_decode($json, true);
    $objects=$objects[0];
    foreach ($objects["class"] as $offer){

          //oferta para un solo cliente
          if($offer["type"]==0)
          {
            $customer=$repositoryCustomers->findOneBy(["code"=>$offer["salescode"]]);

            if($customer!=NULL)
            {

            $offer_ID=$repositoryOfferPrices->getOfferId($product,$customer,$offer["quantity"],$offer["startingdate"]["date"]);
            if($offer_ID!=NULL){
            //  $output->writeln();
              $offeraxiom=$repositoryOfferPrices->findOneBy(["id"=>$offer_ID]);
              $offeraxiom->setPrice($offer["price"]);
              if ($offer["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                $offeraxiom->setEnd(null);
              }
              else $offeraxiom->setEnd(date_create_from_format("Y-m-d h:i:s.u",$offer["endingdate"]["date"]));
              $this->doctrine->getManager()->merge($offeraxiom);
              $this->doctrine->getManager()->flush();

            }
            else{

              $obj=new ERPOfferPrices();
              $obj->setProduct($product);
              $obj->setCustomer($customer);
              $obj->setCompany($company);
              $obj->setType(2);
              $obj->setPrice($offer["price"]);
              $obj->setQuantity($offer["quantity"]);
              $obj->setStart(date_create_from_format("Y-m-d h:i:s.u",$offer["startingdate"]["date"]));
              if ($offer["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                $obj->setEnd(null);
              } else $obj->setEnd(date_create_from_format("Y-m-d h:i:s.u",$offer["endingdate"]["date"]));
              $obj->setDateadd(new \Datetime());
              $obj->setDateupd(new \Datetime());
              $obj->setActive(1);
              $obj->setDeleted(0);
              $this->doctrine->getManager()->merge($obj);
              $this->doctrine->getManager()->flush();
            }


            }
          }
          //oferta para todos los clientes
          else{

            $offer_ID=$repositoryOfferPrices->getOfferId($product,NULL,$offer["quantity"],$offer["startingdate"]["date"]);
            if($offer_ID!=NULL){
              $offeraxiom=$repositoryOfferPrices->findOneBy(["id"=>$offer_ID]);
              $offeraxiom->setPrice($offer["price"]);
              if ($offer["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                $offeraxiom->setEnd(null);
              }
              else $offeraxiom->setEnd(date_create_from_format("Y-m-d h:i:s.u",$offer["endingdate"]["date"]));
              $this->doctrine->getManager()->merge($offeraxiom);
              $this->doctrine->getManager()->flush();

            }

            else{
              $obj=new ERPOfferPrices();
              $obj->setProduct($product);
              $obj->setCompany($company);
              $obj->setType(2);
              $obj->setPrice($offer["price"]);
              $obj->setQuantity($offer["quantity"]);
              $obj->setStart(date_create_from_format("Y-m-d h:i:s.u",$offer["startingdate"]["date"]));
              if ($offer["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                $obj->setEnd(null);
              } else $obj->setEnd(date_create_from_format("Y-m-d h:i:s.u",$offer["endingdate"]["date"]));
              $obj->setDateadd(new \Datetime());
              $obj->setDateupd(new \Datetime());
              $obj->setActive(1);
              $obj->setDeleted(0);
              $this->doctrine->getManager()->merge($obj);
              $this->doctrine->getManager()->flush();


            }



          }

      }
      $this->doctrine->getManager()->clear();
   }
   //------   Critical Section END   ------
   //------   Remove Lock Mutex    ------
   fclose($fp);
}

public function importVariants(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  $fp = fopen('/tmp/axiom-navisionGetProducts-importVariants.lock', 'c');
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de variantes: El proceso ya esta en ejecución.');
    exit;
  }
  //------   Critical Section START   ------
  $repository=$this->doctrine->getRepository(ERPVariantsValues::class);
  $output->writeln('* Importando variantes....');
  $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
  $repositoryVariant=$this->doctrine->getRepository(ERPVariants::class);
  $variants=$repositoryVariant->findAll();
  foreach ($variants as $variant){
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getVariants.php?variant='.$variant->getName());
      $output->writeln('        -Importando valores de la variante '.$variant->getName());
      $objects=json_decode($json, true);
      $objects=$objects[0]["class"];
      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
      foreach ($objects as $object){
        $variantValue;
        if ($variant->getName()=="Color") {
          if ($object["value"]=="AMARILLO C") $variantValue="Amarillo Claro";
          else if ($object["value"]=="AMARILLO F") $variantValue="Amarillo Fluor";
          else if ($object["value"]=="AMARILLO L") $variantValue="Amarillo Limon";
          else if ($object["value"]=="AMARILLO R") $variantValue="Amarillo Real";
          else if ($object["value"]=="ARENA VIGO") $variantValue="Arena Vigore";
          else if ($object["value"]=="AZUL COBAL") $variantValue="Azul Cobalto";
          else if ($object["value"]=="AZUL LUMIN") $variantValue="Azul Luminoso";
          else if ($object["value"]=="AZUL MARIN") $variantValue="Azul Marino";
          else if ($object["value"]=="AZUL ULTA") $variantValue="Azul Ultramar";
          else if ($object["value"]=="BEIGE 585" or $object["value"]=="BEIGE") $variantValue="Beige";
          else if ($object["value"]=="BLANCO 501"or $object["value"]=="BLANCO") $variantValue="Blanco";
          else if ($object["value"]=="BLANCO BRI") $variantValue="Blanco Brillo";
          else if ($object["value"]=="BLANCOPERL") $variantValue="Blanco Perla";
          else if ($object["value"]=="CREMA 586" or $object["value"]=="CREMA") $variantValue="Crema";
          else if ($object["value"]=="GAMUZA 543" or $object["value"]=="GAMUZA") $variantValue="Gamuza";
          else if ($object["value"]=="GRIS AZULA") $variantValue="Gris Azulado";
          else if ($object["value"]=="GRIS OSCUR") $variantValue="Gris Oscuro";
          else if ($object["value"]=="GRIS VIGOR") $variantValue="Gris Vigore";
          else if ($object["value"]=="MALVA MAST") $variantValue="Malva Master";
          else if ($object["value"]=="MARFIL 528" or $object["value"]=="MARFIL") $variantValue="Marfil";
          else if ($object["value"]=="MARRON TAB") $variantValue="Marron Tabaco";
          else if ($object["value"]=="MARRONVINT") $variantValue="Marron Vintage";
          else if ($object["value"]=="NARANJA CL") $variantValue="Naranja Claro";
          else if ($object["value"]=="NARANJA FL") $variantValue="Naranja Fluor";
          else if ($object["value"]=="NEGRO 567" or $object["value"]=="NEGRO") $variantValue="Negro";
          else if ($object["value"]=="VERDE CARR") $variantValue="Verde Carruajes";
          else if ($object["value"]=="NEGRO BRIL") $variantValue="Negro Brillo";
          else if ($object["value"]=="OCRE" or $object["value"]=="OCRE 587") $variantValue="Ocre";
          else if ($object["value"]=="PARDO" or $object["value"]=="PARDO 517") $variantValue="Pardo";
          else if ($object["value"]=="RAYAS GRAN") $variantValue="Rayas Granate";
          else if ($object["value"]=="RAYAS NEGR") $variantValue="Rayas Negras";
          else if ($object["value"]=="ROJO BURDE") $variantValue="Rojo Burdeos";
          else if ($object["value"]=="ROJO CARRU") $variantValue="Rojo Carruaje";
          else if ($object["value"]=="ROJO INGLE") $variantValue="Rojo Ingles";
          else if ($object["value"]=="ROJOIMPERI") $variantValue="Rojo Imperial";
          else if ($object["value"]=="ROSA PALID") $variantValue="Rosa Palido";
          else if ($object["value"]=="SALMON OSC") $variantValue="Salmon Oscuro";
          else if ($object["value"]=="TURQUESA C") $variantValue="Turquesa Claro";
          else if ($object["value"]=="VERDE BOSQ") $variantValue="Verde Bosque";
          else if ($object["value"]=="VERDE CLAR") $variantValue="Verde Claro";
          else if ($object["value"]=="VERDE FRON") $variantValue="Verde Fronton";
          else if ($object["value"]=="VERDE HIER") $variantValue="Verde Hierba";
          else if ($object["value"]=="VERDE PIST") $variantValue="Verde Pistacho";
          else if ($object["value"]=="VERDE PRIM") $variantValue="Verde Primavera";
          else if ($object["value"]=="VINTAGE RO") $variantValue="Vintage Rose";
          else $variantValue=$object["value"];
        } else $variantValue=$object["value"];

        $obj=$repository->findOneBy(["name"=>$variantValue]);
        if ($obj==null){
          $obj=new ERPVariantsValues();
          $obj->setVariantName($variant);
          $obj->setName($variantValue);
          $obj->setDateadd(new \Datetime());
          $obj->setDateupd(new \Datetime());
          $obj->setDeleted(0);
          $obj->setActive(1);
        }
        $this->doctrine->getManager()->merge($obj);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();
      }
    }
    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);
}

public function importProductsVariants(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  $fp = fopen('/tmp/axiom-navisionGetProducts-importProductsVariants.lock', 'c');
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de agrupados: El proceso ya esta en ejecución.');
    exit;
  }
  //------   Critical Section START   ------
  $repository=$this->doctrine->getRepository(ERPProductsVariants::class);
  $output->writeln('* Importando productos agrupados....');
  $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
  $this->importVariants($input, $output);
  $repositoryVariant=$this->doctrine->getRepository(ERPVariants::class);
  $variants=$repositoryVariant->findAll();
  foreach($variants as $variant){
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProductsVariants.php?variant='.$variant->getName());
      $objects=json_decode($json, true);
      $objects=$objects[0]["class"];
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
      $output->writeln('* Importando productos agrupados por '.$variant->getName());
      foreach ($objects as $object){
        $repositoryProduct=$this->doctrine->getRepository(ERPProducts::class);
        $product=$repositoryProduct->findOneBy(["code"=>$object["product"]]);
        $repositoryVariantValue=$this->doctrine->getRepository(ERPVariantsValues::class);
        $nameVariantValue;
        //$output->writeln('       - Añadiendo variante a '.$object["product"]);
        if ($variant->getName()=="Color") {
          if ($object["Code"]=="AMARILLO C") $nameVariantValue="Amarillo Claro";
          else if ($object["Code"]=="AMARILLO F") $nameVariantValue="Amarillo Fluor";
          else if ($object["Code"]=="AMARILLO L") $nameVariantValue="Amarillo Limon";
          else if ($object["Code"]=="AMARILLO R") $nameVariantValue="Amarillo Real";
          else if ($object["Code"]=="ARENA VIGO") $nameVariantValue="Arena Vigore";
          else if ($object["Code"]=="AZUL COBAL") $nameVariantValue="Azul Cobalto";
          else if ($object["Code"]=="AZUL LUMIN") $nameVariantValue="Azul Luminoso";
          else if ($object["Code"]=="AZUL MARIN") $nameVariantValue="Azul Marino";
          else if ($object["Code"]=="AZUL ULTA") $nameVariantValue="Azul Ultramar";
          else if ($object["Code"]=="BEIGE 585" or $object["Code"]=="BEIGE") $nameVariantValue="Beige";
          else if ($object["Code"]=="BLANCO 501"or $object["Code"]=="BLANCO") $nameVariantValue="Blanco";
          else if ($object["Code"]=="BLANCO BRI") $nameVariantValue="Blanco Brillo";
          else if ($object["Code"]=="BLANCOPERL") $nameVariantValue="Blanco Perla";
          else if ($object["Code"]=="CREMA 586" or $object["Code"]=="CREMA") $nameVariantValue="Crema";
          else if ($object["Code"]=="GAMUZA 543" or $object["Code"]=="GAMUZA") $nameVariantValue="Gamuza";
          else if ($object["Code"]=="GRIS AZULA") $nameVariantValue="Gris Azulado";
          else if ($object["Code"]=="GRIS OSCUR") $nameVariantValue="Gris Oscuro";
          else if ($object["Code"]=="GRIS VIGOR") $nameVariantValue="Gris Vigore";
          else if ($object["Code"]=="MALVA MAST") $nameVariantValue="Malva Master";
          else if ($object["Code"]=="MARFIL 528" or $object["Code"]=="MARFIL") $nameVariantValue="Marfil";
          else if ($object["Code"]=="MARRON TAB") $nameVariantValue="Marron Tabaco";
          else if ($object["Code"]=="MARRONVINT") $nameVariantValue="Marron Vintage";
          else if ($object["Code"]=="NARANJA CL") $nameVariantValue="Naranja Claro";
          else if ($object["Code"]=="NARANJA FL") $nameVariantValue="Naranja Fluor";
          else if ($object["Code"]=="NEGRO 567" or $object["Code"]=="NEGRO") $nameVariantValue="Negro";
          else if ($object["Code"]=="VERDE CARR") $nameVariantValue="Verde Carruajes";
          else if ($object["Code"]=="NEGRO BRIL") $nameVariantValue="Negro Brillo";
          else if ($object["Code"]=="OCRE" or $object["Code"]=="OCRE 587") $nameVariantValue="Ocre";
          else if ($object["Code"]=="PARDO" or $object["Code"]=="PARDO 517") $nameVariantValue="Pardo";
          else if ($object["Code"]=="RAYAS GRAN") $nameVariantValue="Rayas Granate";
          else if ($object["Code"]=="RAYAS NEGR") $nameVariantValue="Rayas Negras";
          else if ($object["Code"]=="ROJO BURDE") $nameVariantValue="Rojo Burdeos";
          else if ($object["Code"]=="ROJO CARRU") $nameVariantValue="Rojo Carruaje";
          else if ($object["Code"]=="ROJO INGLE") $nameVariantValue="Rojo Ingles";
          else if ($object["Code"]=="ROJOIMPERI") $nameVariantValue="Rojo Imperial";
          else if ($object["Code"]=="ROSA PALID") $nameVariantValue="Rosa Palido";
          else if ($object["Code"]=="SALMON OSC") $nameVariantValue="Salmon Oscuro";
          else if ($object["Code"]=="TURQUESA C") $nameVariantValue="Turquesa Claro";
          else if ($object["Code"]=="VERDE BOSQ") $nameVariantValue="Verde Bosque";
          else if ($object["Code"]=="VERDE CLAR") $nameVariantValue="Verde Claro";
          else if ($object["Code"]=="VERDE FRON") $nameVariantValue="Verde Fronton";
          else if ($object["Code"]=="VERDE HIER") $nameVariantValue="Verde Hierba";
          else if ($object["Code"]=="VERDE PIST") $nameVariantValue="Verde Pistacho";
          else if ($object["Code"]=="VERDE PRIM") $nameVariantValue="Verde Primavera";
          else if ($object["Code"]=="VINTAGE RO") $nameVariantValue="Vintage Rose";
          else $nameVariantValue=$object["Code"];
        } else $nameVariantValue=$object["Code"];


        $variantValue=$repositoryVariantValue->findOneBy(["variantname"=>$variant, "name"=>$nameVariantValue]);
        $obj=$repository->findOneBy(["variantvalue"=>$variantValue, "product"=>$product]);
        if ($obj==null and $product!=null){
            $output->writeln('* Asignando la variante '.$object["Code"].' al producto '.$object["product"]);
            if ($product->getGrouped()==0) $product->setGrouped(1);
            $obj=new ERPProductsVariants();
            $obj->setProduct($product);
            $obj->setVariantvalue($variantValue);
            $obj->setVariantname($variant);
            $obj->setDateadd(new \Datetime());
            $obj->setDateupd(new \Datetime());
            $obj->setDeleted(0);
            $obj->setActive(1);
            $this->doctrine->getManager()->merge($obj);
            $this->doctrine->getManager()->merge($product);
        }

          $this->doctrine->getManager()->flush();
          $this->doctrine->getManager()->clear();
        }
      }
      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
}


}
?>
