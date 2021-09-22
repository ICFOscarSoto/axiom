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
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPProductPrices;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPShoppingDiscounts;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStores;
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
  private $discordchannel="883046233017552956";
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
      case 'products': {
        $this->importProduct($input, $output);
        //$this->clearEAN13($input, $output);
        $this->importEAN13($input, $output);
        $this->importReferences($input, $output);
        $this->importVariants($input, $output);
        $this->importProductsVariants($input, $output);
      }
      break;
      case 'names': $this->updateNames($input, $output);
      break;
      case 'ean13': $this->importEAN13($input, $output);
      break;
      case 'references': $this->importReferences($input, $output);
      break;
      case 'clearEAN13': $this->clearEAN13($input, $output);
      break;
      case 'prices': $this->importPrices($input, $output);
      break;
      case 'updatePrices': $this->updatePrices($input, $output);
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
      case 'update': $this->updateProducts($input, $output);
      break;
      case 'blocked': $this->disableBlocked($input, $output);
      break;
      case 'ownbarcodes': $this->createOwnBarcodes($input, $output);
      break;
      case 'manufacturers': $this->updateManufacturers($input, $output);
      break;
      case 'group': $this->groupPrices($input, $output);
      break;
      case 'createproducts': $this->createProducts($input, $output);
      break;
      case 'exportnames': $this->exportNames($input, $output);
      break;
      case 'defuse': $this->defuseProducts($input, $output);
      break;
      case 'storesManaged': $this->updateStocksStoresManaged($input, $output);
      break;
      case 'minimumsQuantity': $this->importMinimunsQuantity($input, $output);
      break;
      case 'clear':
        //$this->defuseProducts($input, $output);
        $this->clearEAN13($input, $output);
      break;
      case 'all':
        $this->importProduct($input, $output);
        //$this->clearEAN13($input, $output);
        $this->importVariants($input, $output);
        $this->importProductsVariants($input, $output);
        $this->importEAN13($input, $output);
        $this->importReferences($input, $output);
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
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importProduct.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionGetProducts-importProduct.lock', 'c');
      }
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
      $output->writeln('conseguimos los productos');
      $repositoryCategory=$this->doctrine->getRepository(ERPCategories::class);
      $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
      $repository=$this->doctrine->getRepository(ERPProducts::class);

      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

      foreach ($objects["class"] as $key=>$object){
      /*  if($object["code"]=="1028723")
        {*/
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
          $obj->setDeleted(0);
          $obj->setName($object["Description"]);
          $category=$repositoryCategory->findOneBy(["name"=>"Sin Categoria"]);
          $obj->setCategory($category);
        }
         $supplier=$repositorySupliers->findOneBy(["code"=>$object["Supplier"]]);
         if (strlen($object["Description"])>4) $obj->setName($object["Description"]);
         // Comprobamos si el producto no tiene movimientos desde 2017, en caso de que no tenga lo desactivamos
         $json2=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-clearProducts.php?from='.$object["code"]);
         $movs=json_decode($json2, true);
         $movs=$movs[0];
         /* Dejamos de desactivar productos desde el 2/10

         if($movs["class"][0]["movimiento"]!=null)
         if($movs["class"][0]["movimiento"]["date"]>"2019-09-09 00:00:00.000000" and $object["Blocked"]==0)
            $obj->setActive(1);
            else $obj->setActive(0);
         else $obj->setActive(0); */
         $repositoryTaxes=$this->doctrine->getRepository(GlobaleTaxes::class);
         $taxes=$repositoryTaxes->find(1);
         $obj->setTaxes($taxes);
         $obj->setCode($object["code"]);
         $obj->setCheckweb($object["ProductoWEB"]);
         $obj->setWeight($object["Weight"]);
         $packing=1;
         if ($object["Unidad medida precio"]=='C') $packing=100;
         else if ($object["Unidad medida precio"]=='M') $packing=1000;
         $obj->setPurchasepacking($packing);
         // Comprobamos si el producto tiene descuentos, si no los tiene se le pone como precio neto.
         $json3=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPrices.php?from='.$object["code"].'&supplier='.$object["Supplier"]);
         $prices=json_decode($json3, true);
         $prices=$prices[0];
         $obj->setnetprice(1);
         foreach ($prices["class"] as $price){
           if($price["Discount"]!=0){
             if ($price["Ending"]["date"]=="1753-01-01 00:00:00.000000") {
               $obj->setnetprice(0);
             }
           }
         }
         if (!$obj->getnetprice()){
           $obj->setPVPR($object["ShoppingPrice"]/$obj->getPurchasepacking());
           $obj->setShoppingPrice($obj->getPVPR()*(1-$obj->getShoppingDiscount($this->doctrine)/100));
         } else {
           $obj->setPVPR(0);
           $obj->setShoppingPrice($object["ShoppingPrice"]/$obj->getPurchasepacking());
         }
         $obj->setSupplier($supplier);
         $obj->setDateupd(new \Datetime());
         $repositoryManufacturers=$this->doctrine->getRepository(ERPManufacturers::class);
         $manufacturer=$repositoryManufacturers->findOneBy(["code"=>$object["Manufacturer"]]);
         if($manufacturer!=NULL) $obj->setManufacturer($manufacturer);
         $this->doctrine->getManager()->merge($obj);
         $this->doctrine->getManager()->flush();
         $obj->priceCalculated($this->doctrine);
         $this->doctrine->getManager()->clear();
      /* }*/
      }
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"products"]);
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
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importEAN13.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionGetProducts-importEAN13.lock', 'c');
      }

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
        $nameEAN13=preg_replace('/\D/','',$object["Cross-Reference No."]);
        $obj=$repository->findOneBy(["name"=>$nameEAN13]);
        if ($obj==null and $object["idaxiom"]==null) {
          $output->writeln('  - '.$object["Item No."].' - '.$nameEAN13);
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
            $obj->setType(1);
          } else {$obj->setCustomer($customer);
            $obj->setType(2);
          }
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
      if ($objects["maxtimestamp"]!=0) $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
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
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-clearEAN13.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-clearEAN13.lock', 'c');
  }

  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de limpieza de EAN13: El proceso ya esta en ejecución.');
    exit;
  }
  $repository=$this->doctrine->getRepository(ERPEAN13::class);
  $page=5000;
  $totalEAN13=round(intval($repository->totalEAN13())/$page);
  $count=0;
  while($count<$totalEAN13){
      $EAN13s=$repository->EAN13Limit(intval($count*$page),intval($page));
      $count++;
      foreach ($EAN13s as $id) {
        $EAN13=$repository->findOneBy(["id"=>$id, "company"=>2]);
        if ($EAN13->getType()==1)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-searchEAN13.php?EAN13='.$EAN13->getName().'$crossReferenceNo='.$EAN13->getSupplier()->getCode().'$item='.$EAN13->getProduct()->getCode());
        else if ($EAN13->getType()==2)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-searchEAN13.php?EAN13='.$EAN13->getName().'$crossReferenceNo='.$EAN13->getCustomer()->getCode().'$item='.$EAN13->getProduct()->getCode());

        $objects=json_decode($json, true);
        if ($objects[0]["class"]!=null) continue;
        $output->writeln('* Desactivando la referencia  '.$reference->getName());

        $reference->setActive(0);
        $reference->setDeleted(1);
        $this->doctrine->getManager()->merge($reference);
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
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importPrices.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importPrices.lock', 'c');
  }

  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de precios: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
  $datetime=new \DateTime();
  $output->writeln('* Sincronizando precios....');
  $repositoryCategory=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryShoppingDiscounts=$this->doctrine->getRepository(ERPShoppingDiscounts::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $page=5000;
  $totalProducts=round(intval($repository->totalProducts())/$page);
  $count=0;

  while($count<$totalProducts){
      $products=$repository->productsLimit(intval($count*$page),intval($page));
      $count++;

           foreach($products as $id) {
              $product=$repository->findOneBy(["id"=>$id, "company"=>2]);
              if ($product->getSupplier()==null or $product->getCategory()==null)  continue;
              $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
              $price=$repositoryShoppingDiscounts->findOneBy(["supplier"=>$product->getSupplier(),"category"=>$product->getCategory()]);
              if ($price) $output->writeln("El producto ".$product->getCode()." tiene el precio ". $price->getDiscount());
              else $output->writeln("El producto ".$product->getCode()." no tiene precio");
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
                    if($obj->getEnd()==null) $obj->setShoppingPrices($this->doctrine);
                    $this->doctrine->getManager()->clear();
                  }
                }
              }


          }
          $output->writeln($count);
  }




  //------   Critical Section END   ------
  //------   Remove Lock Mutex    ------
  fclose($fp);
}

public function updatePrices(InputInterface $input, OutputInterface $output){
  $output->writeln('* Actualiando precios cientos en Axiom....');
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-updateProducts.php');
  $objects=json_decode($json, true);
  $objects=$objects[0];
  foreach ($objects["class"] as $object){

    $productsRepository=$this->doctrine->getRepository(ERPProducts::class);
    $product=$productsRepository->findOneBy(["code"=>$object["code"]]);
    $output->writeln("Comprobando precio del producto ".$product->getCode());
    $packing=1;
    if ($object["Unidad medida precio"]=='C') $packing=100;
    else if ($object["Unidad medida precio"]=='M') $packing=1000;
    $product->setPurchasepacking($packing);
    $this->doctrine->getManager()->merge($product);
    $this->doctrine->getManager()->flush();
    $this->doctrine->getManager()->clear();
    /*$product=$productsRepository->findOneBy(["code"=>$object["code"]]);
    $output->writeln("  -> Packing ".$packing);
    if ($product->getNetprice()==0)  $product->setShoppingPrice($product->getPVPR()*(1-$product->getShoppingDiscount($this->doctrine)/100));

    if ($packing!=1){
      $product->setShoppingPrice($object["ShoppingPrice"]/$packing);
    }

    $product->calculatePVP($this->doctrine);

    $product=$product->calculateIncrementByProduct($this->doctrine);
    $product=$product->calculateCustomerIncrementsByProduct($this->doctrine);
    $this->doctrine->getManager()->merge($product);
    $this->doctrine->getManager()->flush();
    $this->doctrine->getManager()->clear();
    $output->writeln("  -> Packing ".$product->getPurchasepacking());*/
  }
}

public function groupPrices(InputInterface $input, OutputInterface $output){
  $repository=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryShopping=$this->doctrine->getRepository(ERPShoppingDiscounts::class);

  $suppliers=$repositorySuppliers->findBy(['id'=>1082]);
  foreach($suppliers as $supplier){
    $prices=$repositoryShopping->findBy(['supplier'=>$supplier, 'active'=>1]);
    foreach ($prices as $price){
      if ($price->getCategory()==null or $price->getCategory()->getParentid()==null) continue;
      $categories=$repository->findSisters($price->getCategory()->getParentid()->getId());
      $count=0;
      foreach($categories as $category){
        $shoppingDiscount=$repositoryShopping->findOneBy(['supplier'=>$supplier,'category'=>$category, 'active'=>1]);
        if ($shoppingDiscount==null or $shoppingDiscount->getDiscount()==$price->getDiscount()) continue;
        else $count=1;
      }
      $newshoppingDiscount=$repositoryShopping->findOneBy(['supplier'=>$supplier,'category'=>$price->getCategory()->getParentid(), 'active'=>1]);
      if ($count==0 and $newshoppingDiscount==null) {
        $output->writeln("Agrupo en ".$price->getCategory()->getParentid()->getName()." cuyo id es ".$price->getCategory()->getParentid()->getId());
        $obj=new ERPShoppingDiscounts();
        $obj->setSupplier($supplier);
        $obj->setCategory($price->getCategory()->getParentid());
        $obj->setDiscount($price->getDiscount());
        $obj->setDiscount1($price->getDiscount1());
        $obj->setDiscount2($price->getDiscount2());
        $obj->setDiscount3($price->getDiscount3());
        $obj->setDiscount4($price->getDiscount4());
        $obj->setQuantity($price->getQuantity());
        $obj->setStart($price->getStart());
        $obj->setEnd($price->getEnd());
        $obj->setDateadd(new \Datetime());
        $obj->setDateupd(new \Datetime());
        $obj->setActive(1);
        $obj->setDeleted(0);
        $this->doctrine->getManager()->merge($obj);
        $this->doctrine->getManager()->flush();
        if($obj->getEnd()==null) $obj->setShoppingPrices($this->doctrine);
        $this->doctrine->getManager()->clear();
        // delete delete delete
        foreach($categories as $category){
          /*$shoppingDiscount=$repositoryShopping->findOneBy(['supplier'=>$supplier,'category'=>$category, 'active'=>1]);
          if ($shoppingDiscount!=null) {  $output->writeln("Elimino ".$shoppingDiscount->getId());$repositoryShopping->deleteShoppingDiscount($shoppingDiscount->getId());}*/
        }
      }
    }
  }
}

public function updateProducts(InputInterface $input, OutputInterface $output){
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $products=$repository->findBy(['shoppingPrice'=>0]);
  $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
  foreach ($products as $product){
    $output->writeln("Cambiando el producto ".$product->getCode());
    $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProduct.php?product='.$product->getCode());
    $objects=json_decode($json, true);
    if ($objects[0]["class"]==null) continue;
    $object=$objects[0]["class"][0];
    $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
    $supplier=$repositorySupliers->findOneBy(["code"=>$object["Supplier"]]);
    // Comprobamos si el producto no tiene movimientos desde 2017, en caso de que no tenga lo desactivamos
    $json2=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-clearProducts.php?from='.$product->getCode());
    $movs=json_decode($json2, true);
    $movs=$movs[0];
    $repositoryTaxes=$this->doctrine->getRepository(GlobaleTaxes::class);
    $taxes=$repositoryTaxes->find(1);
    $product->setTaxes($taxes);
    $product->setWeight($object["Weight"]);
    // Comprobamos si el producto tiene descuentos, si no los tiene se le pone como precio neto.
    $json3=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPrices.php?from='.$product->getCode().'&supplier='.$object["Supplier"]);
    $prices=json_decode($json3, true);
    $prices=$prices[0];
    $product->setnetprice(1);
    foreach ($prices["class"] as $price){
      if($price["Discount"]!=0){
        if ($price["Ending"]["date"]=="1753-01-01 00:00:00.000000") {
          $product->setnetprice(0);
        }
      }
    }
    if (!$product->getnetprice()){
      if ($product->getPurchasepacking()!=0){
        $product->setPVPR($object["ShoppingPrice"]/$product->getPurchasepacking());
        $product->setShoppingPrice(($product->getPVPR()*(1-$product->getShoppingDiscount($this->doctrine)/100))/$product->getPurchasepacking());
      }
      $product->setPVPR($object["ShoppingPrice"]);
      $product->setShoppingPrice($product->getPVPR()*(1-$product->getShoppingDiscount($this->doctrine)/100));
    } else {
      $product->setPVPR(0);
      if ($product->getPurchasepacking()!=0) $product->setShoppingPrice($object["ShoppingPrice"]/$product->getPurchasepacking());
      else $product->setShoppingPrice($object["ShoppingPrice"]);
    }
    $product->setSupplier($supplier);
    $product->setDateupd(new \Datetime());
    $repositoryManufacturers=$this->doctrine->getRepository(ERPManufacturers::class);
    $manufacturer=$repositoryManufacturers->findOneBy(["code"=>$object["Manufacturer"]]);
    if($manufacturer!=NULL) $product->setManufacturer($manufacturer);

    $product->calculatePVP($this->doctrine);
    $this->doctrine->getManager()->merge($product);
    $product=$product->calculateIncrementByProduct($this->doctrine);
    $product=$product->calculateCustomerIncrementsByProduct($this->doctrine);
    $this->doctrine->getManager()->merge($product);
    $this->doctrine->getManager()->flush();
    $this->doctrine->getManager()->clear();
    }
}

public function importStocks(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importStocks.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importStocks.lock', 'c');
  }

  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de los stocks: El proceso ya esta en ejecución.');
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
  $repositoryVariantsValues=$this->doctrine->getRepository(ERPVariantsValues::class);
  $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getStocks.php?from='.$navisionSync->getMaxtimestamp());
  $objects=json_decode($json, true);
  $objects=$objects[0];
    if ($objects){
      $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
      $company=$repositoryCompanies->find(2);
      foreach ($objects["class"] as $stock){
      $product=$repositoryProducts->findOneBy(["code"=>$stock["code"]]);
      $namenameVariantValue=$this->variantColor($stock["variant"]);
      $variantvalue=$repositoryVariantsValues->findOneBy(["name"=>$namenameVariantValue]);
      $storeRepository=$this->doctrine->getRepository(ERPStores::class);
      $store=$storeRepository->findOneBy(["code"=>$stock["almacen"]]);

      if($product) {
            $productVariantId = null;
            $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product->getId(),"variantvalue"=>$variantvalue]);
            if($productvariant!=null) {
              $productVariantId=$productvariant->getId();
              $old_stocks=$repositoryStocks->stockVariantUpdate($productvariant->getId(), $stock["almacen"]);
              $output->writeln('El producto '.$product->getId().' tiene la variante '.$stock["variant"]);
            }
            else $old_stocks=$repositoryStocks->stockUpdate($product->getId(), $stock["almacen"]);

            if($old_stocks[0]["id"]!=null) {
              $stock_old=$repositoryStocks->findOneBy(["id"=>$old_stocks[0]["id"], "deleted"=>0]);
              $output->writeln('Vamos a actualizar la linea '.$old_stocks[0]["id"].' del producto '.$product->getId().' en el almacen '.$stock["almacen"]);
              if ((int)$stock["stock"]<0) $quantity=0;
              else $quantity=(int)$stock["stock"];
              if ($stock_old->getStorelocation()->getStore()->getManaged()!=1) {
                $updateStocks=$repositoryStocks->setZeroStocks($product->getId(), $store->getId(),$stock_old->getId(),$productVariantId);
                $stock_old->setQuantity(!$quantity?0:$quantity);
                $stock_old->setDateupd(new \Datetime());
                $this->doctrine->getManager()->merge($stock_old);
              }
            }
            else {
              $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["almacen"]]);
              if($location!=null){
              $obj=new ERPStocks();
              $obj->setCompany($company);
              $obj->setProduct($product);
              $obj->setDateadd(new \Datetime());
              $obj->setDateupd(new \Datetime());
              $obj->setStoreLocation($location);
              $obj->setProductVariant($productvariant);
              if ((int)$stock["stock"]<0) $quantiy=0;
              else $quantity=(int)$stock["stock"];
              $obj->setQuantity(!$quantity?0:$quantity);
              $obj->setActive(1);
              $obj->setDeleted(0);
              $this->doctrine->getManager()->merge($obj);}
            }
            $this->doctrine->getManager()->flush();
            $this->doctrine->getManager()->clear();
          }

          $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"stocks"]);
          if ($navisionSync==null) {
          $navisionSync=new NavisionSync();
          $navisionSync->setEntity("stocks");
      }


    }
    $navisionSync->setLastsync($datetime);
    if($objects["maxtimestamp"]!=0) $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
    /*else {
    $icon=":warning: ";
    $msg=" ";
    //Send notification
    file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode($icon."Sincronizacion : ".$msg));
  } */
    $this->doctrine->getManager()->persist($navisionSync);
    $this->doctrine->getManager()->flush();
  }

    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);
  }


    /* Solo se añaden las lineas de traspasos realizadas en Navision en los almacenes gestionados*/
public function updateStocksStoresManaged(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  //$fp = fopen('/tmp/axiom-navisionGetProducts-importIncrements.lock', 'c');
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-updateStocksStoresManaged.lock.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-updateStocksStoresManaged.lock.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de almacenes gestionados: El proceso ya esta en ejecución.');
    exit;
  }
  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"storesManaged"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Sincronizando almacenes gestionados....');
  $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
  $repositoryVariantsValues=$this->doctrine->getRepository(ERPVariantsValues::class);
  $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
  $repositoryStocks=$this->doctrine->getRepository(ERPStocks::class);
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getStocksManaged.php?from='.$navisionSync->getMaxtimestamp());
  $objects=json_decode($json, true);
  $objects=$objects[0];
  if ($objects){
    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $company=$repositoryCompanies->find(2);
    foreach ($objects["class"] as $stock){
      $product=$repositoryProducts->findOneBy(["code"=>$stock["code"]]);
      $namenameVariantValue=$this->variantColor($stock["variant"]);
      $variantvalue=$repositoryVariantsValues->findOneBy(["name"=>$namenameVariantValue]);

      if($product) {
            $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product->getId(),"variantvalue"=>$variantvalue]);
            if($productvariant!=null) {
              $old_stocks=$repositoryStocks->stockVariantUpdate($productvariant->getId(), $stock["almacen"]);
              $output->writeln('El producto '.$product->getId().' tiene la variante '.$stock["variant"]);
            }
            else $old_stocks=$repositoryStocks->stockUpdate($product->getId(), $stock["almacen"]);

            if($old_stocks[0]["id"]!=null) {
              $stock_old=$repositoryStocks->findOneBy(["id"=>$old_stocks[0]["id"], "deleted"=>0]);
              $output->writeln('Vamos a actualizar la linea '.$old_stocks[0]["id"].' del producto '.$product->getId().' en el almacen '.$stock["almacen"]);
              if ($stock_old->getStorelocation()->getStore()->getManaged()==1) {
              $stock_old->setQuantity($stock_old->getQuantity()-((int)$stock["stock"]));
              $stock_old->setDateupd(new \Datetime());
              $this->doctrine->getManager()->merge($stock_old);}
            }
            else {
              $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["almacen"]]);
              if($location!=null){
              $obj=new ERPStocks();$obj->setCompany($company);
              $obj->setProduct($product);
              $obj->setDateadd(new \Datetime());
              $obj->setDateupd(new \Datetime());
              $obj->setStoreLocation($location);
              $obj->setProductVariant($productvariant);
              if ((int)$stock["stock"]<0) $quantiy=0;
              else $quantity=(int)$stock["stock"];
              $obj->setQuantity(!$quantity?0:$quantity);
              $obj->setActive(1);
              $obj->setDeleted(0);
              $output->writeln('Vamos a crear la linea del producto '.$product->getId().' en el almacen '.$stock["almacen"]);
              $this->doctrine->getManager()->merge($obj);}
            }
            $this->doctrine->getManager()->flush();
            $this->doctrine->getManager()->clear();
          }
        }

          $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"storesManaged"]);
          $navisionSync->setLastsync($datetime);
          if ($objects["maxEntry"]!=0) $navisionSync->setMaxtimestamp($objects["maxEntry"]);
          $this->doctrine->getManager()->persist($navisionSync);
          $this->doctrine->getManager()->flush();

    }



}
public function importIncrements(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  //$fp = fopen('/tmp/axiom-navisionGetProducts-importIncrements.lock', 'c');
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importIncrements.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importIncrements.lock', 'c');
  }
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
  $repositoryCategory=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
  $repositoryCustomeGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
  $repositoryIncrements=$this->doctrine->getRepository(ERPIncrements::class);
  $repositoryCustomerIncrements=$this->doctrine->getRepository(ERPCustomerIncrements::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $repositoryproductprices=$this->doctrine->getRepository(ERPProductPrices::class);
  $repositorycustomerprices=$this->doctrine->getRepository(ERPCustomerPrices::class);
  $page=5000;
  $totalProducts=round(intval($repository->totalProductsCategory())/$page);
  $count=0;

  while($count<$totalProducts){
      $products=$repository->productsLimitActive(intval($count*$page),intval($page));
      $count++;

  //Disable SQL logger
    foreach($products as $id) {
    $product=$repository->findOneBy(["id"=>$id, "company"=>2]);
    $company=$repositoryCompanies->find(2);
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
    $output->writeln($product->getCode().'  - '.$product->getName());
    if ($product->getCategory()!=null && $product->getSupplier()!=null){
      $supplier=$repositorySupliers->findOneBy(["id"=>$product->getSupplier()->getId()]);
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getIncrements.php?product='.$product->getCode());
      $objects=json_decode($json, true);
      $objects=$objects[0];
      foreach ($objects["class"] as $increment){
        if($increment["neto"]==0) continue;
      //grupos de clientes
      if($increment["type"]==1){
          $customergroup=$repositoryCustomeGroups->findOneBy(["name"=>$increment["salescode"]]);
          if($customergroup!=NULL){
              $incrementaxiom_ID=$repositoryIncrements->getIncrementIdByGroup($product->getSupplier(), $product->getCategory(), $customergroup);
              //no existe el incremento en axiom
              if($incrementaxiom_ID==null)  {
                $output->writeln('Añadimos el incremento para el grupo '.$increment["salescode"]);
                if($increment["Discount"]!=0 AND $increment["neto"]!=0){
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
                    $obj->setIncrement(round($inc));
                    $obj->setDateadd(new \Datetime());
                    $obj->setDateupd(new \Datetime());
                    $obj->setActive(1);
                    $obj->setDeleted(0);
                    $this->doctrine->getManager()->persist($obj);
                    $this->doctrine->getManager()->flush();
                    $output->writeln('Actualizamos todos los productos asociados a ese incremento...');
                    $obj->calculateIncrementsBySupplierCategory($this->doctrine);
                }
              }
              //existe el incremento en axiom, luego hay que editarlo siempre y cuando haya habido alguna modificación
              else{
                $output->writeln('Ya existe el incremento de grupo '.$incrementaxiom_ID["id"]);
                $incrementaxiom=$repositoryIncrements->findOneBy(["id"=>$incrementaxiom_ID]);
                $pvp=$increment["pvp"];
                $dto=$increment["Discount"];
                $neto=$increment["neto"];
                $precio_con_dto=$pvp-$pvp*($dto/100);
                $inc=round((($precio_con_dto/$neto)-1)*100,2);
                //antes de hacer ninguna modificación, comprobamos si ha habido algún cambio en el incremento, de no ser así, no se hace nada.
                if(round($incrementaxiom->getIncrement(),2)!=$inc){
                  $output->writeln('Actualizamos el incremento de grupo '.$incrementaxiom_ID["id"]);
                  $incrementaxiom->setIncrement($inc);
                  $incrementaxiom->setDateupd(new \Datetime());
                  $this->doctrine->getManager()->persist($incrementaxiom);
                  $this->doctrine->getManager()->flush();
                }

              //  $output->writeln(date("Y-m-d"));
              //  $output->writeln(date("Y-m-d", strtotime($incrementaxiom->getDateupd()->format('Y-m-d'))));

                if(date("Y-m-d")!=date("Y-m-d", strtotime($incrementaxiom->getDateupd()->format('Y-m-d'))))
                {
                  $output->writeln('Actualizamos los precios del incremento '.$incrementaxiom_ID["id"]);
                  $incrementaxiom->calculateIncrementsBySupplierCategory($this->doctrine);
                }
                else   $output->writeln('Ya hemos actualizado los productos con este incremento');
              }
            }
          }
      //cliente concreto
      else if($increment["type"]==0){
            $customer=$repositoryCustomers->findOneBy(["code"=>$increment["salescode"]]);
            if($customer!=NULL){
                $customerincrementaxiom_ID=$repositoryCustomerIncrements->getIncrementIdByCustomer($product->getSupplier(),$product->getCategory(),$customer);
                //no existe el incremento para el cliente, luego lo creamos
                if($customerincrementaxiom_ID==null){
                  $output->writeln('Añadimos incremento para el cliente '.$increment["salescode"]);
                  if($increment["Discount"]!=0 AND $increment["neto"]!=0){
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
                      $this->doctrine->getManager()->persist($obj);
                      $this->doctrine->getManager()->flush();
                      $output->writeln('Actualizamos todos los productos asociados a ese incremento...');
                      $obj->calculateIncrementsBySupplierCategory($this->doctrine);
                  }
              }
              //ya existe el descuento para ese cliente
              else{
                $output->writeln('Ya existe el incremento de cliente '.$customerincrementaxiom_ID);
                $customerincrementaxiom=$repositoryCustomerIncrements->findOneBy(["id"=>$customerincrementaxiom_ID]);
                $pvp=$increment["pvp"];
                $dto=$increment["Discount"];
                $neto=$increment["neto"];
                $precio_con_dto=$pvp-$pvp*($dto/100);
                $inc=round((($precio_con_dto/$neto)-1)*100,2);
                //antes de hacer ninguna modificación, comprobamos si ha habido algún cambio en el incremento, de no ser así, no se hace nada.
                if(round($customerincrementaxiom->getIncrement(),2)!=$inc){
                  $output->writeln('Actualizamos el incremento de cliente '.$customerincrementaxiom_ID);
                  $customerincrementaxiom->setIncrement($inc);
                  $customerincrementaxiom->setDateupd(new \Datetime());
                  $customerincrementaxiom->setStart(date_create_from_format("Y-m-d h:i:s.u",$increment["startingdate"]["date"]));
                  if ($increment["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                    $customerincrementaxiom->setEnd(null);
                  } else $customerincrementaxiom->setEnd(date_create_from_format("Y-m-d h:i:s.u",$increment["endingdate"]["date"]));
                  $this->doctrine->getManager()->persist($customerincrementaxiom);
                  $this->doctrine->getManager()->flush();
                }

                $output->writeln('Actualizamos los precios del incremento cliente '.$customerincrementaxiom_ID);
                $customerincrementaxiom->calculateIncrementsBySupplierCategory($this->doctrine);


                if(date("Y-m-d")!=date("Y-m-d", strtotime($customerincrementaxiom->getDateupd()->format('Y-m-d'))))
                {
                    $output->writeln('Actualizamos los precios del incremento cliente '.$customerincrementaxiom_ID);
                    $customerincrementaxiom->calculateIncrementsBySupplierCategory($this->doctrine);
                }
                else   $output->writeln('Ya hemos actualizado los productos con este incremento');
              }
            }
            $output->writeln('Finalizado el incremento para el cliente');
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



public function importMinimunsQuantity(InputInterface $input, OutputInterface $output){
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getMinimumsQuantity.php');
  $objects=json_decode($json, true);
  $objects=$objects[0];
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  foreach ($objects["class"] as $object){
    $product=$repository->findOneBy(["code"=>$object["code"]]);
    $product->setMinimumquantityofsale($object["minimo"]);
    $this->doctrine->getManager()->persist($product);
    $this->doctrine->getManager()->flush();
  }
  $this->doctrine->getManager()->clear();

}
public function importOffers(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importOffers.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importOffers.lock', 'c');
  }


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

  $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
  $repositoryOfferPrices=$this->doctrine->getRepository(ERPOfferPrices::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $products=$repository->findBy(["active"=>1]);
  //Disable SQL logger
 foreach($products as $product) {
  // $product=$repository->findOneBy(["code"=>'0202031006']);
    $output->writeln($product->getCode().'  - '.$product->getName());
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
    $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getOffers.php?product='.$product->getCode());
    $objects=json_decode($json, true);
    $objects=$objects[0];
    $company=$repositoryCompanies->find(2);

    foreach ($objects["class"] as $offer){

          //oferta para un solo cliente
          if($offer["type"]==0)
          {
            $customer=$repositoryCustomers->findOneBy(["code"=>$offer["salescode"]]);

            if($customer!=NULL)
            {
              $offer_ID=$repositoryOfferPrices->getOfferId($product,$customer,(int)round($offer["quantity"]),round($offer["price"],2));
              if($offer_ID!=NULL){
                $output->writeln("Existe la oferta");
                $offeraxiom=$repositoryOfferPrices->findOneBy(["id"=>$offer_ID]);
                $offeraxiom->setPrice($offer["price"]);
                if ($offer["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                  $offeraxiom->setEnd(null);
                }
                else $offeraxiom->setEnd(date_create_from_format("Y-m-d h:i:s.u",$offer["endingdate"]["date"]));
                //dump($offeraxiom);
                $this->doctrine->getManager()->persist($offeraxiom);
                $output->writeln("Actualizamos la oferta");

              }
            else{
              $output->writeln("No existe la oferta");
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

              $this->doctrine->getManager()->persist($obj);
              $output->writeln("Creamos la oferta");
            }


            }
          }
          //oferta para todos los clientes
          else{

            $offer_ID=$repositoryOfferPrices->getOfferId($product,NULL,(int)round($offer["quantity"]),round($offer["price"],2));
            if($offer_ID!=NULL){
              $offeraxiom=$repositoryOfferPrices->findOneBy(["id"=>$offer_ID]);
              $offeraxiom->setPrice($offer["price"]);
              if ($offer["endingdate"]["date"]=="1753-01-01 00:00:00.000000") {
                $offeraxiom->setEnd(null);
              }
              else $offeraxiom->setEnd(date_create_from_format("Y-m-d h:i:s.u",$offer["endingdate"]["date"]));
              $this->doctrine->getManager()->persist($offeraxiom);
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
              $this->doctrine->getManager()->persist($obj);
              $this->doctrine->getManager()->flush();


            }



          }


      }

   }
     $this->doctrine->getManager()->clear();
   //------   Critical Section END   ------
   //------   Remove Lock Mutex    ------
   fclose($fp);
}

public function importVariants(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importVariants.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importVariants.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion incrementos: El proceso ya esta en ejecución.');
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
        if ($variant->getName()=="Color") $variantValue=$this->variantColor($object["value"]);
        else if ($variant->getName()=="Fragancia") $variantValue=$this->variantFragrance($object["value"]);
        else $variantValue=$object["value"];

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
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importProductsVariants.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importProductsVariants.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion incrementos: El proceso ya esta en ejecución.');
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
        if ($variant->getName()=="Color") $nameVariantValue=$this->variantColor($object["Code"]);
        else if ($variant->getName()=="Fragancia") $nameVariantValue=$this->variantFragrance($object["Code"]);
        else $nameVariantValue=$object["Code"];


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
public function variantFragrance($nameVariantValue){
  if ($nameVariantValue=="BLUE SILVE") $nameVariantValue="Blue Silver";
  if ($nameVariantValue=="FOREVER YO") $nameVariantValue="Forever Young";
  if ($nameVariantValue=="NEUTRALIZA") $nameVariantValue="Neutralizador";
  else $nameVariantValue=ucwords(strtolower($nameVariantValue));
  return $nameVariantValue;
}
public function variantColor($nameVariantValue){
  if ($nameVariantValue=="AMARILLO C") $nameVariantValue="Amarillo Claro";
  else if ($nameVariantValue=="AMARILLO F") $nameVariantValue="Amarillo Fluor";
  else if ($nameVariantValue=="AMARILLO L") $nameVariantValue="Amarillo Limon";
  else if ($nameVariantValue=="AMARILLO R") $nameVariantValue="Amarillo Real";
  else if ($nameVariantValue=="ARENA VIGO") $nameVariantValue="Arena Vigore";
  else if ($nameVariantValue=="AZUL COBAL") $nameVariantValue="Azul Cobalto";
  else if ($nameVariantValue=="AZUL LUMIN") $nameVariantValue="Azul Luminoso";
  else if ($nameVariantValue=="AZUL MARIN") $nameVariantValue="Azul Marino";
  else if ($nameVariantValue=="AZUL ULTA") $nameVariantValue="Azul Ultramar";
  else if ($nameVariantValue=="BEIGE 585" or $nameVariantValue=="BEIGE") $nameVariantValue="Beige";
  else if ($nameVariantValue=="BLANCO 501" or $nameVariantValue=="BLANCO" or $nameVariantValue=="BLANCA") $nameVariantValue="Blanco";
  else if ($nameVariantValue=="BLANCO BRI") $nameVariantValue="Blanco Brillo";
  else if ($nameVariantValue=="BLANCOPERL") $nameVariantValue="Blanco Perla";
  else if ($nameVariantValue=="CREMA 586" or $nameVariantValue=="CREMA") $nameVariantValue="Crema";
  else if ($nameVariantValue=="GAMUZA 543" or $nameVariantValue=="GAMUZA") $nameVariantValue="Gamuza";
  else if ($nameVariantValue=="GRIS AZULA") $nameVariantValue="Gris Azulado";
  else if ($nameVariantValue=="GRIS OSCUR") $nameVariantValue="Gris Oscuro";
  else if ($nameVariantValue=="GRIS VIGOR") $nameVariantValue="Gris Vigore";
  else if ($nameVariantValue=="MALVA MAST") $nameVariantValue="Malva Master";
  else if ($nameVariantValue=="MARFIL 528" or $nameVariantValue=="MARFIL") $nameVariantValue="Marfil";
  else if ($nameVariantValue=="MARRON TAB") $nameVariantValue="Marron Tabaco";
  else if ($nameVariantValue=="MARRONVINT") $nameVariantValue="Marron Vintage";
  else if ($nameVariantValue=="NARANJA CL") $nameVariantValue="Naranja Claro";
  else if ($nameVariantValue=="NARANJA FL") $nameVariantValue="Naranja Fluor";
  else if ($nameVariantValue=="NEGRO 567" or $nameVariantValue=="NEGRO") $nameVariantValue="Negro";
  else if ($nameVariantValue=="VERDE CARR") $nameVariantValue="Verde Carruajes";
  else if ($nameVariantValue=="NEGRO BRIL") $nameVariantValue="Negro Brillo";
  else if ($nameVariantValue=="OCRE" or $nameVariantValue=="OCRE 587") $nameVariantValue="Ocre";
  else if ($nameVariantValue=="PARDO" or $nameVariantValue=="PARDO 517") $nameVariantValue="Pardo";
  else if ($nameVariantValue=="RAYAS GRAN") $nameVariantValue="Rayas Granate";
  else if ($nameVariantValue=="RAYAS NEGR") $nameVariantValue="Rayas Negras";
  else if ($nameVariantValue=="ROJO BURDE") $nameVariantValue="Rojo Burdeos";
  else if ($nameVariantValue=="ROJO CARRU") $nameVariantValue="Rojo Carruaje";
  else if ($nameVariantValue=="ROJO INGLE") $nameVariantValue="Rojo Ingles";
  else if ($nameVariantValue=="ROJOIMPERI") $nameVariantValue="Rojo Imperial";
  else if ($nameVariantValue=="ROSA PALID") $nameVariantValue="Rosa Palido";
  else if ($nameVariantValue=="SALMON OSC") $nameVariantValue="Salmon Oscuro";
  else if ($nameVariantValue=="TURQUESA C") $nameVariantValue="Turquesa Claro";
  else if ($nameVariantValue=="VERDE BOSQ") $nameVariantValue="Verde Bosque";
  else if ($nameVariantValue=="VERDE CLAR") $nameVariantValue="Verde Claro";
  else if ($nameVariantValue=="VERDE FRON") $nameVariantValue="Verde Fronton";
  else if ($nameVariantValue=="VERDE HIER") $nameVariantValue="Verde Hierba";
  else if ($nameVariantValue=="VERDE PIST") $nameVariantValue="Verde Pistacho";
  else if ($nameVariantValue=="VERDE PRIM") $nameVariantValue="Verde Primavera";
  else if ($nameVariantValue=="VINTAGE RO") $nameVariantValue="Vintage Rose";
  else $nameVariantValue=ucwords(strtolower($nameVariantValue));
  return $nameVariantValue;
}

public function disableBlocked(InputInterface $input, OutputInterface $output){
  $repository=$this->doctrine->getRepository(ERPEAN13::class);
  $datetime=new \DateTime();
  $output->writeln('* Limpiando Products....');
  $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProducts.php');
  $objects=json_decode($json, true);
  $output->writeln($objects);
  /*foreach ($objects as $object){
    $repositoryProduct=$this->doctrine->getRepository(ERPProducts::class);
    $product=$repositoryProduct->findOneBy(["code"=>$object["code"]]);
    $product->setActive(0);
    $output->writeln('* Bloqueando....'.$product);
    $this->doctrine->getManager()->merge($product);
  }*/
  $this->doctrine->getManager()->flush();
  $this->doctrine->getManager()->clear();
}

public function importReferences(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importReferences.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importReferences.lock', 'c');
  }

  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de Referencias Cruzadas: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"References"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Sincronizando References....');
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getReferences.php?from='.$navisionSync->getMaxtimestamp());
  $objects=json_decode($json, true);
  $objects=$objects[0];

  $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
  $repositorySupliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
  $repository=$this->doctrine->getRepository(ERPReferences::class);
  //Disable SQL logger
  $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
  foreach ($objects["class"] as $key=>$object){
    //$reference=preg_replace('/\D/','',$object["Cross-Reference No."]);
    $product=$repositoryProducts->findOneBy(["code"=>$object["Item No."]]);
    if ($product!=null) {
    $obj=$repository->findOneBy(["name"=>$object["Cross-Reference No."], "product"=>$product->getId()]);
    if ($obj==null){
      $output->writeln('  -Añadiendo al producto '.$object["Item No."].' la referencia '.$object["Cross-Reference No."]);
      $obj=new ERPReferences();
      $obj->setName($object["Cross-Reference No."]);
      $obj->setDescription($object["Description"]);
      $obj->setDateadd(new \Datetime());
      $obj->setDateupd(new \Datetime());
        $obj->setProduct($product);
      $obj->setDeleted(0);
      $obj->setActive(1);
      if ($object["Cross-Reference Type"]==2){
        $supplier=$repositorySupliers->findOneBy(["code"=>$object["Cross-Reference Type No."]]);
        $obj->setSupplier($supplier);
        $obj->setType(1);
      } else if ($object["Cross-Reference Type"]==1){
        $customer=$repositoryCustomers->findOneBy(["code"=>$object["Cross-Reference Type No."]]);
        $obj->setCustomer($customer);
        $obj->setType(2);
      }
      $this->doctrine->getManager()->merge($obj);
      $this->doctrine->getManager()->flush();
      $this->doctrine->getManager()->clear();
    }
    else {
      $output->writeln('  -Modificando la referencia '.$object["Cross-Reference No."].' del producto '.$object["Item No."]);
      $obj->setDescription($object["Description"]);
      $this->doctrine->getManager()->merge($obj);
      $this->doctrine->getManager()->flush();
      $this->doctrine->getManager()->clear();
    }
  }
  }
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"References"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setEntity("References");
  }
  $navisionSync->setLastsync($datetime);
  $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
  $this->doctrine->getManager()->persist($navisionSync);
  $this->doctrine->getManager()->flush();
  //------   Critical Section END   ------
  //------   Remove Lock Mutex    ------
  fclose($fp);
}

public function clearReferences(InputInterface $input, OutputInterface $output){
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-clearReferences.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-clearReferences.lock', 'c');
  }

  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la limpieza de referencias cruzadas: El proceso ya esta en ejecución.');
    exit;
  }

  $repository=$this->doctrine->getRepository(ERPReferences::class);
  $page=5000;
  $totalReferences=round(intval($repository->totalReferences())/$page);
  $count=0;
  while($count<$totalReferences){
      $references=$repository->referencesLimit(intval($count*$page),intval($page));
      $count++;
      foreach ($references as $id) {
        $reference=$repository->findOneBy(["id"=>$id, "company"=>2]);
        if ($reference->getType()==1)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getReference.php?reference='.$reference->getName().'$crossReferenceNo='.$reference->getSupplier()->getCode());
        else if ($reference->getType()==2)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getReference.php?reference='.$reference->getName().'$crossReferenceNo='.$reference->getCustomer()->getCode());

        $objects=json_decode($json, true);
        if ($objects[0]["class"]!=null) continue;
        $output->writeln('* Desactivando la referencia  '.$reference->getName());

        $reference->setActive(0);
        $reference->setDeleted(1);
        $this->doctrine->getManager()->merge($reference);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();
      }
  }

}

public function createOwnBarcodes(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-createOwnBarcodes.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-createOwnBarcodes.lock', 'c');
  }

  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la creación de codigos barras propios en Navision: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"ownbarcodes"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
  $repositoryVariants=$this->doctrine->getRepository(ERPProductsVariants::class);

  $products=$repositoryProducts->findAll();
  foreach($products as $product){
    $barcodes[]=["product_code"=>$product->getCode(), "barcode"=>'P.'.str_pad($product->getId(),8,'0', STR_PAD_LEFT), "axiom_id"=>$product->getId()];
    $variants=$repositoryVariants->findBy(["product"=>$product, "deleted"=>0]);
    foreach($variants as $variant){
      $barcodes[]=["product_code"=>$product->getCode(), "barcode"=>'V.'.str_pad($variant->getId(),8,'0', STR_PAD_LEFT), "axiom_id"=>$product->getId()];
    }
  }
  foreach($barcodes as $barcode){
    $output->writeln('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createEAN132.php?json='.json_encode($barcode));
    $result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createEAN132.php?json='.json_encode($barcode));
  }

  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"ownbarcodes"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setEntity("ownbarcodes");
  }
  $navisionSync->setLastsync($datetime);
  $navisionSync->setMaxtimestamp($datetime->getTimestamp());
  $this->doctrine->getManager()->persist($navisionSync);
  $this->doctrine->getManager()->flush();
  //------   Critical Section END   ------
  //------   Remove Lock Mutex    ------
  fclose($fp);

}

public function updateNames(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-updateNames.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-updateNames.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la actualización de nombres: El proceso ya esta en ejecución.');
    exit;
  }
  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"updateNames"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setEntity("updateNames");
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Actualizando nombres....');
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-updateNamesProducts.php?from='.$navisionSync->getMaxtimestamp());
  $objects=json_decode($json, true);
  $objects=$objects[0];
  foreach ($objects["class"] as $object){
    $productsRepository=$this->doctrine->getRepository(ERPProducts::class);
    $product=$productsRepository->findOneBy(["code"=>$object["code"]]);
    if ($product!=null){
      $output->writeln('* Actualizando el nombre del producto '.$object["code"]);
      $json2=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProduct.php?product='.$object["code"]);
      $objects2=json_decode($json2, true);
      $objects2=$objects2[0];
      if (!empty($objects2["class"]) and strlen($objects2["class"][0]["Description"])) $product->setName($objects2["class"][0]["Description"]);
      $product->setDateupd(new \Datetime());
      $this->doctrine->getManager()->merge($product);
      $this->doctrine->getManager()->flush();
      $this->doctrine->getManager()->clear();
    }

  }

  $navisionSync->setLastsync($datetime);
  $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
  $this->doctrine->getManager()->persist($navisionSync);
  $this->doctrine->getManager()->flush();
}

public function updateManufacturers(InputInterface $input, OutputInterface $output){
  //------   Create Lock Mutex    ------
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-updateManufacturers.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-updateManufacturers.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la actualización de nombres: El proceso ya esta en ejecución.');
    exit;
  }
  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"updateManufacturers"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setEntity("updateManufacturers");
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Actualizando marcas....');
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-updateManufacturersProducts.php?from='.$navisionSync->getMaxtimestamp());
  $objects=json_decode($json, true);
  $objects=$objects[0];
  foreach ($objects["class"] as $object){
    $productsRepository=$this->doctrine->getRepository(ERPProducts::class);
    $manufacturersRepository=$this->doctrine->getRepository(ERPManufacturers::class);
    $product=$productsRepository->findOneBy(["code"=>$object["code"]]);
    if ($product!=null){
      $output->writeln('* Actualizando la marca del producto '.$object["code"]);
      $marca_new=str_replace(" ","",($object["manufacturer"]));
      if($marca_new=="BONFIGLI") $marca_new="BONFIGLIOLI";
      if($marca_new=="JPANADERO") $marca_new="JUAN PANADERO";
      $manufacturer=$manufacturersRepository->findOneBy(["code"=>$marca_new]);
      $product->setManufacturer($manufacturer);
      $product->setDateupd(new \Datetime());
      $this->doctrine->getManager()->merge($product);
      $this->doctrine->getManager()->flush();
      $this->doctrine->getManager()->clear();
    }

  }

  $navisionSync->setLastsync($datetime);
  $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
  $this->doctrine->getManager()->persist($navisionSync);
  $this->doctrine->getManager()->flush();
}

public function exportNames(InputInterface $input, OutputInterface $output){
  $repository=$this->doctrine->getRepository(ERPProducts::class);

  $page=5000;
  $totalProducts=round(intval($repository->totalProducts())/$page);
  $count=0;

  while($count<$totalProducts){
      $products=$repository->productsLimit(intval($count*$page),intval($page));
      $count++;
        foreach ($products as $id){
        $product=$repository->findOneBy(["id"=>$id]);
        $code=$product->getCode();
        $Description=substr($product->getName(),0,30);
        $Description2=substr($product->getName(),30,30);
        $output->writeln('Actualizando el producto '.$code);
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-exportNames.php?code='.$code.'&desc1='.urlencode($Description).'&desc2='.urlencode($Description2));
      }
    }
}

public function createProducts(InputInterface $input, OutputInterface $output){
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-createProducts.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-createProducts.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la creación de productos en Navision.');
    exit;
  }
  //------   Critical Section START   ------
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"createProducts"]);
  if ($navisionSync==null) {
    $navisionSync=new NavisionSync();
    $navisionSync->setEntity("createProducts");
    $navisionSync->setMaxtimestamp(0);
  }
  $datetime=new \DateTime();
  $output->writeln('* Creando productos en Navision....');
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $product_ids=$repository->getProductsToNavision();
  $item=[];
  $array_products=[];
    foreach($product_ids as $product_id)
    {
        $product_obj=$repository->findOneBy(["id"=>$product_id["id"]]);
        $repositorysuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
        if($product_obj->getSupplier()!=null) $supplier=$repositorysuppliers->findOneBy(["id"=>$product_obj->getSupplier()->getId()]);
        else $supplier=null;
        $repositoryreferences=$this->doctrine->getRepository(ERPReferences::class);
        if($supplier!=null) $supplier_reference=$repositoryreferences->findOneBy(["product"=>$product_obj,"supplier"=>$supplier]);
        else $supplier_reference=null;
        $item["code"]=$product_obj->getCode();
        $item["name"]=$product_obj->getName();
        $item["description"]=$product_obj->getDescription();
        $item["onsale"]=$product_obj->getOnsale();
        $item["active"]=$product_obj->getActive();
        $item["deleted"]=$product_obj->getDeleted();
        $item["manufacturer"]=$product_obj->getManufacturer()?$product_obj->getManufacturer()->getCode():'';
        $item["pvp"]=$product_obj->getPVP();
        $item["shoppingPrice"]=$product_obj->getShoppingPrice();
        $item["vendor"]=$supplier?$supplier->getCode():'';
        $item["vendoritem"]=$supplier_reference?$supplier_reference->getName():'';
        $item["checkweb"]=$product_obj->getCheckweb();
        $item["dateupd"]=$product_obj->getDateupd();

        $array_products=$item;
        $json = json_encode($array_products);

        $result=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-createProduct.php?json='.urlencode($json));
        $output->writeln($result);
        /*
        $ch = curl_init($this->url.'navisionExport/axiom/do-NAVISION-createProduct.php');
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $json );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        */
        $array_products=[];
        $item=[];

      /*
     }
     */
  }

}

public function defuseProducts(InputInterface $input, OutputInterface $output){
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $page=5000;
  $totalProducts=round(intval($repository->totalProducts())/$page);
  $count=0;

  while($count<$totalProducts){
      $products=$repository->productsLimit(intval($count*$page),intval($page));
      $count++;
      foreach ($products as $id) {
        $product=$repository->findOneBy(["id"=>$id, "company"=>2]);
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProduct.php?product='.$product->getCode());
        $objects=json_decode($json, true);
        if ($objects[0]["class"]!=null) continue;
        $output->writeln('* Desactivando el producto '.$product->getCode());
        $repository->deleteRelations($product->getId());
        $product->setActive(0);
        $product->setDeleted(1);
        $this->doctrine->getManager()->merge($product);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();
      }

    }

  }
}
?>
