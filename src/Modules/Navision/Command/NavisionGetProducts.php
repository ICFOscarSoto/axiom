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
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPProductPrices;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProductsSuppliersDiscounts;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPIncrements;
use App\Modules\ERP\Entity\ERPOfferPrices;
use App\Modules\ERP\Entity\ERPCustomerIncrements;
use App\Modules\ERP\Entity\ERPCustomerPrices;
use App\Modules\ERP\Entity\ERPVariantsTypes;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Entity\ERPProductsSuppliers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
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
            ->addArgument('code', InputArgument::OPTIONAL, '¿Objeto que sincronizar?')
        ;
  }
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    $entity = $input->getArgument('entity');
    $code = $input->getArgument('code');
    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $this->company=$repositoryCompanies->find(2);
    $output->writeln('');
    $output->writeln('Comenzando sincronizacion Navision');
    $output->writeln('==================================');
    switch($entity){
      case 'productsChange':{
        $this->importProduct($input, $output);
      }
      break;
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
      case 'incrementsCustomers': $this->importIncrementsCustomers($input, $output);
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
      case 'storesManagedNew' : $this->importStocksStoresManaged($input, $output);
      break;
      case 'minimumsQuantity': $this->importMinimunsQuantity($input, $output);
      break;
      case 'productStock': $this->importStock($input, $output, $code);
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
      $fp = null;
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionChangeGetProducts-importProduct.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionChangeGetProducts-importProduct.lock', 'c');
      }
      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la sincronizacion de productos: El proceso ya esta en ejecución.');
        exit;
      }

      //------   Critical Section START   ------
      $output->writeln('* Sincronizando productos....');
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetProducts.php');
      $objects=json_decode($json, true);

      $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
      $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
      $repositoryCategories=$this->doctrine->getRepository(ERPCategories::class);
      $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);

      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

      $deleteProductChange = [];
      foreach ($objects as $key=>$object){
        $action   = $object['accion'];
        $code_old = $object['codigo_antiguo'];
        $code_new = $object['codigo_nuevo'];
        $product  = null;
        if (isset($object['producto']))
          $product=$object['producto'];
        $oproduct = null;
        // Borrado de producto
        if ($action=='D') {
            $oproduct=$repositoryProducts->findOneBy(["code"=>$code_old]);
            if ($oproduct!=NULL){
                $output->writeln($action.' - '.$code_old);
                /*$oproduct->setDeleted(1);
                $oproduct->setActive(0);
                $this->doctrine->getManager()->persist($oproduct);
                $this->doctrine->getManager()->flush();
                $this->doctrine->getManager()->clear();*/
                // Sumar producto al json para eliminar en tabla de cambios
                if (isset($object['producto']))
                  unset($object['producto']);
                $deleteProductChange[] = $object;
            }
        }else{
        if ($product!=null && isset($product["code"]) && $product["code"]!=null){
            $output->writeln($action.' - '.$code_old.' - '.$code_new.' - '.$product["Description"]);
            // Inserta nuevo producto
            if ($action=='I') {
              // Por si existiera
              $oproduct=$repositoryProducts->findOneBy(["code"=>$code_new]);
            }else
            if ($action=='U') {
              // Si no existe se hace lo mismo que el insert
              $oproduct=$repositoryProducts->findOneBy(["code"=>$code_old]);
              if ($oproduct==null){
                $oproduct=$repositoryProducts->findOneBy(["code"=>$code_new]);
              }else{
                // Si ya existe el código nuevo se usa este
                $oproduct_new=$repositoryProducts->findOneBy(["code"=>$code_new]);
                if ($oproduct_new!=null)
                  $oproduct = $oproduct_new;
              }
            }
            if ($oproduct==null){
              $oproduct = new ERPProducts();
              $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
              $oproduct->setCompany($repositoryCompanies->find(2));
              $oproduct->setDateadd(new \Datetime());
              $oproduct->setDeleted(0);
              $oproduct->setName($product["Description"]);
              $category=$repositoryCategories->findOneBy(["name"=>"Sin Categoria"]);
              $oproduct->setCategory($category);
            }
            $oproduct->setCode($product["code"]);
            $supplier=$repositorySuppliers->findOneBy(["code"=>$product["Supplier"]]);
            if (strlen($product["Description"])>4) $oproduct->setName($product["Description"]);
            $repositoryTaxes=$this->doctrine->getRepository(GlobaleTaxes::class);
            $taxes=$repositoryTaxes->find(1);
            $oproduct->setTaxes($taxes);
            $oproduct->setCheckweb($product["ProductoWEB"]);

            // Comprobamos si el producto tiene descuentos, si no los tiene se le pone como precio neto.
            $json3=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPrices.php?from='.$code_new.'&supplier='.$product["Supplier"]);
            $prices=json_decode($json3, true);
            $prices=$prices[0];
            $oproduct->setnetprice(1);
            foreach ($prices["class"] as $price){
              if($price["Discount"]!=0){
                if ($price["Ending"]["date"]=="1753-01-01 00:00:00.000000") {
                   $oproduct->setnetprice(0);
                }
              }
            }
            $packing=1;
            if ($product["Unidad medida precio"]=='C') $packing=100;
            else if ($product["Unidad medida precio"]=='M') $packing=1000;
            if (!$oproduct->getnetprice()){
              $oproduct->setPVPR($product["ShoppingPrice"]/$packing);
              $oproduct->setShoppingPrice($oproduct->getPVPR()*(1-$oproduct->getShoppingDiscount($this->doctrine,$oproduct->getSupplier())/100));
            } else {
               $oproduct->setPVPR(0);
               $oproduct->setShoppingPrice($product["ShoppingPrice"]/$packing);
            }
            $oproduct->setSupplier($supplier);
            $oproduct->setDateupd(new \Datetime());
            $repositoryManufacturers=$this->doctrine->getRepository(ERPManufacturers::class);
            $manufacturer=$repositoryManufacturers->findOneBy(["code"=>$product["Manufacturer"]]);
            if($manufacturer!=NULL) $oproduct->setManufacturer($manufacturer);
            $oproduct->priceCalculated($this->doctrine);
            $this->doctrine->getManager()->persist($oproduct);
            //$this->doctrine->getManager()->flush();
            //$this->doctrine->getManager()->clear();

            // ERPProductsVariants tiene weight y purchasepacking se crea siempre para la variante null (es decir la de por defecto del producto)
            // y si ya existiera se modifica
            $weight = ($product["Weight"]!='')?$product["Weight"]:null;
            $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>null]);
            if ($oproductvariant==null){
              $oproductvariant = new ERPProductsVariants();
              $oproductvariant->setProduct($oproduct);
              $oproductvariant->setVariant(null);
              $oproductvariant->setDateadd(new \Datetime());
            }
            $oproductvariant->setActive(1);
            $oproductvariant->setDeleted(0);
            $oproductvariant->setDateupd(new \Datetime());
            $oproductvariant->setWeight($weight);
            $oproductvariant->setPurchasepacking($packing);
            if ($oproductvariant->getVariant()!=null && $oproductvariant->getVariant()->getId()==null)
              $oproductvariant->setVariant(null);
            $this->doctrine->getManager()->persist($oproductvariant);
            $this->doctrine->getManager()->flush();
            $this->doctrine->getManager()->clear();

            // Sumar producto al json para eliminar en tabla de cambios
            if (isset($object['producto']))
              unset($object['producto']);
            $deleteProductChange[] = $object;
          }
        }
      }
      // Eliminado de tabla de cambios
      $output->writeln('Eliminar cambios realizados....');
      $postdata = http_build_query(
          array(
              'deleteProductsChange' => json_encode($deleteProductChange)
          )
      );
      $opts = array('http' =>
          array(
              'method'  => 'POST',
              'header'  => 'Content-Type: application/x-www-form-urlencoded',
              'content' => $postdata
          )
      );
      $context = stream_context_create($opts);
      file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetProductsDelete.php',false,$context);
      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
    }

public function importStocksStoresManaged(InputInterface $input, OutputInterface $output){
  $fp = null;
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionChangeGetTransfers.lock', 'c');
  } else {
    $fp = fopen('/tmp/axiom-navisionChangeGetTransfers.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion de traspasos: El proceso ya esta en ejecución.');
    exit;
  }

  //------   Critical Section START   ------
  $output->writeln('* Sincronizando traspasos....');
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetTransfers.php');
  $objects=json_decode($json, true);
  $deleteTransfersChange=null;

  foreach ($objects as $object){
    $repositoryStocks=$this->doctrine->getRepository(ERPStocks::class);
    $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
    $repositoryVariants=$this->doctrine->getRepository(ERPVariants::class);
    $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
    $repositoryStoreLocations=$this->doctrine->getRepository(ERPStoreLocations::class);
    $repositoryStores=$this->doctrine->getRepository(ERPStores::class);
    $old_obj=explode('~',$object['codigo_antiguo']);
    $new_obj=explode('~',$object['codigo_nuevo']);
    $productvariant = null;
    $product=null;
    $variant=null;
    $storelocation=null;
    $store=null;
    if($new_obj[2]!="") $variant=$repositoryVariants->findOneBy(["name"=>$new_obj[2]]);
    if ($object['accion']=='U') {
      $quantity=intval($new_obj[3])-intval($old_obj[3]);
      $product=$repositoryProducts->findOneBy(["code"=>$new_obj[1]]);
      $store=$repositoryStores->findOneBy(["code"=>$new_obj[5]]);
      $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product,"variant"=>$variant]);
      $storelocation=$repositoryStoreLocations->findOneBy(["store"=>$store]);
      $stocks=$repositoryStocks->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation, "active"=>1, "deleted"=>0]);
      if($product!=null AND $storelocation!=null)
      {
        if ($stocks==null ){
          $stocks=new ERPStocks();
          $stocks->setProductVariant($productvariant);
          $stocks->setStoreLocation($storelocation);
          $stocks->setCompany($this->company);
          $stocks->setQuantity(0);
          $stocks->setPendingreceive($quantity);
          $stocks->setDateupd(new \Datetime());
          $stocks->setDateadd(new \Datetime());
          $stocks->setDeleted(0);
          $stocks->setActive(1);
        } else {
        $stocks->setPendingreceive($stocks->getPendingreceive()-intval($old_obj[3])+$new_obj[3]);
        }
        $this->doctrine->getManager()->persist($stocks);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();
      }
    }
    else if ($object['accion']=='D'){
      $quantity=$old_obj[3];
      $product=$repositoryProducts->findOneBy(["code"=>$old_obj[1]]);
      $store=$repositoryStores->findOneBy(["code"=>$old_obj[5]]);
      $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product,"variant"=>$variant]);
      $storelocation=$repositoryStoreLocations->findOneBy(["store"=>$store]);
      $stocks=$repositoryStocks->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation, "active"=>1, "deleted"=>0]);
      if($product!=null AND $storelocation!=null)
      {
        if ($stocks==null ){
          $stocks=new ERPStocks();
          $stocks->setProductVariant($productvariant);
          $stocks->setStoreLocation($storelocation);
          $stocks->setCompany($this->company);
          $stocks->setQuantity(0);
          $stocks->setPendingreceive($quantity);
          $stocks->setDateupd(new \Datetime());
          $stocks->setDateadd(new \Datetime());
          $stocks->setDeleted(0);
          $stocks->setActive(1);
        } else {
        $stocks->setPendingreceive($stocks->getPendingreceive()-$quantity);
        }
        $this->doctrine->getManager()->persist($stocks);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();
      }
    }
    // Sumar producto al json para eliminar en tabla de cambios
      $deleteTransfersChange[] = $object;

  }


  // Eliminado de tabla de cambios

  if($deleteTransfersChange!=null){
    $output->writeln('Eliminar cambios realizados....');
    $postdata = http_build_query(
        array(
            'deleteTransfersChange' => json_encode($deleteTransfersChange)
        )
    );
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context = stream_context_create($opts);
    file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetTransfersDelete.php',false,$context);
    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
  }

  fclose($fp);

}

public function importEAN13(InputInterface $input, OutputInterface $output){
      //------   Create Lock Mutex    ------
      $fp = null;
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
      $output->writeln('* Sincronizando EAN13....');
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetEAN13.php');
      $objects=json_decode($json, true);

      $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
      $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
      $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
      $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
      $repositoryVariants=$this->doctrine->getRepository(ERPVariants::class);
      $repositoryEAN13=$this->doctrine->getRepository(ERPEAN13::class);

      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

      $deleteEAN13Change = [];
      $validation = true;
      foreach ($objects as $key=>$object){
        $action     = $object['accion'];
        if ($object['codigo_antiguo']!=$object['codigo_nuevo']){
          $adatos_old = explode('~',$object['codigo_antiguo']);
          $code_old   = null;
          $product_old = null;
          $variant_old = null;
          $type_old   = null;
          $supplier_customer_old   = null;
          if (count($adatos_old)>=4){
            $code_old   = preg_replace('/\D/','',$adatos_old[0]);
            $product_old = $adatos_old[1];
            $type_old   = $adatos_old[2];
            $supplier_customer_old = $adatos_old[3];
          }
          if (count($adatos_old)==5){
            $variant_old = $adatos_old[4];
          }
          $adatos_new = explode('~',$object['codigo_nuevo']);
          $code_new   = null;
          $product_new = null;
          $variant_new = null;
          $type_new   = null;
          $supplier_customer_new   = null;
          if (count($adatos_new)>=4){
            $code_new   = preg_replace('/\D/','',$adatos_new[0]);
            $product_new = $adatos_new[1];
            $type_new   = $adatos_new[2];
            $supplier_customer_new = $adatos_new[3];
          }
          if (count($adatos_new)==5){
            $variant_new = $adatos_new[4];
          }
          $ean13  = null;
          if (isset($object['ean13']))
            $ean13=$object['ean13'];
          $oean13 = null;

          // Borrado de EAN13
          if ($action=='D') {
              $oproduct=$repositoryProducts->findOneBy(["code"=>$product_old]);
              $ovariant=null;
              if ($variant_old!='')
                $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_old]);
              $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);

              if ($oproductvariant!=null){
                $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_old]);
                if ($customer==null){
                  $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_old]);
                  if ($supplier!=null)
                    $oean13=$repositoryEAN13->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "supplier"=>$supplier, "customer"=>null, "type"=>1]);
                  else
                    $oean13=$repositoryEAN13->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>null, "type"=>null]);
                }else {
                  $oean13=$repositoryEAN13->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>$customer, "type"=>2]);
                }
                if ($oean13!=null){
                  $output->writeln($action.' - '.$code_old);
                  $this->doctrine->getManager()->remove($oean13);
                  $this->doctrine->getManager()->flush();
                  $this->doctrine->getManager()->clear();
                }
              }
          }else{
             $output->writeln($action.' - '.$code_old.' - '.$code_new.' - '.$product_new.' - '.$supplier_customer_new);
             // Inserta nuevo EAN13
             if ($action=='I') {
               $oproduct=$repositoryProducts->findOneBy(["code"=>$product_new]);
               $ovariant=null;
               if ($variant_new!='')
                 $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_new]);
               $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);

               if ($oproductvariant!=null){
                 $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_new]);
                 if ($customer==null){
                   $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_new]);
                   if ($supplier!=null)
                     $oean13=$repositoryEAN13->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "supplier"=>$supplier, "customer"=>null, "type"=>1]);
                   else
                     $oean13=$repositoryEAN13->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>null, "type"=>null]);
                 }else {
                   $oean13=$repositoryEAN13->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>$customer, "type"=>2]);
                 }
               }
             }else
             if ($action=='U') {
               // Si no existe se hace lo mismo que el insert
               $oproduct=$repositoryProducts->findOneBy(["code"=>$product_old]);
               $ovariant=null;
               if ($variant_old!='')
                 $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_old]);
               $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);

               if ($oproductvariant!=null){
                 $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_old]);
                 if ($customer==null){
                   $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_old]);
                   if ($supplier!=null)
                     $oean13=$repositoryEAN13->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "supplier"=>$supplier, "customer"=>null, "type"=>1]);
                   else{
                     $oean13=$repositoryEAN13->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>null, "type"=>null]);
                   }
                 }else {
                   $oean13=$repositoryEAN13->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>$customer, "type"=>2]);
                 }
                 // Si no existe se busca el nuevo para ver si está
                if ($oean13==null){
                  $oproduct=$repositoryProducts->findOneBy(["code"=>$product_new]);
                  $ovariant=null;
                  if ($variant_new!='')
                    $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_new]);
                  $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);

                  if ($oproductvariant!=null){
                    $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_new]);
                    if ($customer==null){
                      $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_new]);
                      if ($supplier!=null)
                        $oean13=$repositoryEAN13->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "supplier"=>$supplier, "customer"=>null, "type"=>1]);
                      else
                        $oean13=$repositoryEAN13->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>null, "type"=>null]);
                    }else {
                      $oean13=$repositoryEAN13->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "supplier"=>null, "customer"=>$customer, "type"=>2]);
                    }
                  }
                }
               }
             }
             if ($oean13==null){
                $oean13=new ERPEAN13();
                $oean13->setDateadd(new \Datetime());
             }
              $oean13->setName($code_new);
              $oean13->setDateupd(new \Datetime());
              $oean13->setDeleted(0);
              $oean13->setActive(1);
              $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_new]);
              if ($customer==null){
                $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_new]);
                if ($supplier!=null){
                  $oean13->setSupplier($supplier);
                  $oean13->setCustomer(null);
                  $oean13->setType(1);
                }
              } else {
                $oean13->setSupplier(null);
                $oean13->setCustomer($customer);
                $oean13->setType(2);
              }
              $oproduct=$repositoryProducts->findOneBy(["code"=>$product_new]);
              $ovariant=null;
              if ($variant_new!='')
                $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_new]);
              $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);
              if ($oproductvariant!=null) {
                $oean13->setProductvariant($oproductvariant);
                $this->doctrine->getManager()->persist($oean13);
                $this->doctrine->getManager()->flush();
                $this->doctrine->getManager()->clear();
              }
          }
        }
        // Sumar EAN13 al json para eliminar en tabla de cambios
        if (isset($object['ean13']))
          unset($object['ean13']);
          $deleteEAN13Change[] = $object;
      }
      // Eliminado de tabla de cambios
      $output->writeln('Eliminar cambios realizados....');
      $postdata = http_build_query(
          array(
              'deleteEAN13Change' => json_encode($deleteEAN13Change)
          )
      );
      $opts = array('http' =>
          array(
              'method'  => 'POST',
              'header'  => 'Content-Type: application/x-www-form-urlencoded',
              'content' => $postdata
          )
      );
      $context = stream_context_create($opts);
      file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetEAN13Delete.php',false,$context);
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
        $EAN13=$repository->findOneBy(["id"=>$id, "company"=>$this->company]);
        if ($EAN13->getType()==1)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-searchEAN13.php?EAN13='.$EAN13->getName().'$crossReferenceNo='.$EAN13->getSupplier()->getCode().'$item='.$EAN13->getProduct()->getCode());
        else if ($EAN13->getType()==2)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-searchEAN13.php?EAN13='.$EAN13->getName().'$crossReferenceNo='.$EAN13->getCustomer()->getCode().'$item='.$EAN13->getProduct()->getCode());

        $objects=json_decode($json, true);
        if ($objects[0]["class"]!=null) continue;
        $output->writeln('* Desactivando la referencia  '.$reference->getName());

        $reference->setActive(0);
        $reference->setDeleted(1);
        $this->doctrine->getManager()->persist($reference);
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
  $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryProductsuppliersdiscounts=$this->doctrine->getRepository(ERPProductsSuppliersDiscounts::class);
  $productsVariantsRepository=$this->doctrine->getRepository(ERPproductsVariants::class);
  $productsSuppliersRepository=$this->doctrine->getRepository(ERPProductsSuppliers::class);
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $page=5000;
  $totalProducts=round(intval($repository->totalProductsCategory())/$page);
  $count=0;

  while($count<$totalProducts){
      $products=$repository->productsLimitCategory(intval($count*$page),intval($page));
      $count++;
      foreach($products as $id) {
        $product=$repository->findOneBy(["id"=>$id, "company"=>$this->company]);
        $productvariant=$productsVariantsRepository->findOneBy(["product"=>$product, "variant"=>null]);
        if ($product->getSupplier()==null or $product->getCategory()==null)  continue;
        $productsSuppliers=$productsSuppliersRepository->findBy(["productvariant"=>$productvariant, "active"=>1, "deleted"=>0]);
        foreach ($productsSuppliers as $productSupplier){
          $supplier=$productSupplier->getSupplier();
          $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
          $discount=$repositoryProductsuppliersdiscounts->findOneBy(["supplier"=>$supplier,"category"=>$product->getCategory(), "active"=>1, "deleted"=>0]);
          if ($discount) $output->writeln("El producto ".$product->getCode()." tiene el descuento ". $discount->getDiscount()." para el proveedor ".$supplier->getCode());
          else $output->writeln("El producto ".$product->getCode()." no tiene descuentos activos");
          if ($discount==null && $supplier!=null){
            $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPrices.php?from='.$product->getCode().'&supplier='.$supplier->getCode());
            $objects=json_decode($json, true);
            $objects=$objects[0];
              foreach ($objects["class"] as $prices){
                if($prices["Discount"]!=0){
                  if ($prices["Ending"]["date"]=="1753-01-01 00:00:00.000000") $dateend=null;
                  else $dateend=date_create_from_format("Y-m-d h:i:s.u",$prices["Ending"]["date"]);
                  $datestart=date_create_from_format("Y-m-d h:i:s.u",$prices["Starting"]["date"]);
                  $productsuppliersdiscounts=$repositoryProductsuppliersdiscounts->findOneBy(["supplier"=>$supplier,"category"=>$product->getCategory(), "discount"=>$prices["Discount"], "start"=>$datestart, "end"=>$dateend]);
                  if ($productsuppliersdiscounts!=null) continue;
                  $category=$repositoryCategory->findOneBy(["id"=>$product->getCategory()->getId()]);
                  if ($category==null) $output->writeln(' --> El producto '.$product->getCode().' esta anadiendo el precio '.$prices["Discount"].' al proveedor '.$supplier->getCode());
                  else $output->writeln(' --> El producto '.$product->getCode().' esta anadiendo el precio '.$prices["Discount"].' al proveedor '.$supplier->getCode().' en la categoria '.$category->getName());
                    $obj=new ERPProductsSuppliersDiscounts();
                    $obj->setSupplier($supplier);
                    $obj->setCategory($category);
                    $obj->setDiscount($prices["Discount"]);
                    $obj->setDiscount1($prices["Discount1"]);
                    $obj->setDiscount2($prices["Discount2"]);
                    $obj->setDiscount3($prices["Discount3"]);
                    $obj->setDiscount4($prices["Discount4"]);
                    $obj->setQuantity($prices["Quantity"]);
                    $obj->setStart($datestart);
                    $obj->setEnd($dateend);
                    $obj->setQuantity(1);
                    $obj->setDateadd(new \Datetime());
                    $obj->setDateupd(new \Datetime());
                  if (strtotime($prices["Ending"]["date"])<strtotime(date("d-m-Y H:i:00",time())) && $prices["Ending"]["date"]!="1753-01-01 00:00:00.000000" ) {
                    $obj->setActive(0);
                  } else {
                    $obj->setActive(1);
                  }
                  $obj->setDeleted(0);
                  $this->doctrine->getManager()->persist($obj);
                  $this->doctrine->getManager()->flush();
                  if($obj->getEnd()==null) $obj->setShoppingPrices($this->doctrine, $supplier);
                  $this->doctrine->getManager()->clear();
                  }
                }
              }
            }
          }
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
    /*$packing=1;
    if ($object["Unidad medida precio"]=='C') $packing=100;
    else if ($object["Unidad medida precio"]=='M') $packing=1000;
    $product->setPurchasepacking($packing);*/
    // TODO ERPProductsVariants tiene el purchasepacking
    /*$this->doctrine->getManager()->persist($product);
    $this->doctrine->getManager()->flush();
    $this->doctrine->getManager()->clear();*/
    /*$product=$productsRepository->findOneBy(["code"=>$object["code"]]);
    $output->writeln("  -> Packing ".$packing);
    if ($product->getNetprice()==0)  $product->setShoppingPrice($product->getPVPR()*(1-$product->getShoppingDiscount($this->doctrine)/100));

    if ($packing!=1){
      $product->setShoppingPrice($object["ShoppingPrice"]/$packing);
    }

    $product->calculatePVP($this->doctrine);

    $product=$product->calculateIncrementByProduct($this->doctrine);
    $product=$product->calculateCustomerIncrementsByProduct($this->doctrine);
    $this->doctrine->getManager()->persist($product);
    $this->doctrine->getManager()->flush();
    $this->doctrine->getManager()->clear();
    $output->writeln("  -> Packing ".$product->getPurchasepacking());*/
  }
}

public function groupPrices(InputInterface $input, OutputInterface $output){
  $repository=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryProductsuppliersdiscounts=$this->doctrine->getRepository(ERPProductsSuppliersDiscounts::class);

  $suppliers=$repositorySuppliers->findBy(['id'=>1082]);
  foreach($suppliers as $supplier){
    $prices=$repositoryProductsuppliersdiscounts->findBy(['supplier'=>$supplier, 'active'=>1]);
    foreach ($prices as $price){
      if ($price->getCategory()==null or $price->getCategory()->getParentid()==null) continue;
      $categories=$repository->findSisters($price->getCategory()->getParentid()->getId());
      $count=0;
      foreach($categories as $category){
        $productsuppliersdiscounts=$repositoryProductsuppliersdiscounts->findOneBy(['supplier'=>$supplier,'category'=>$category, 'active'=>1]);
        if ($productsuppliersdiscounts==null or $productsuppliersdiscounts->getDiscount()==$price->getDiscount()) continue;
        else $count=1;
      }
      $newproductsuppliersdiscounts=$repositoryProductsuppliersdiscounts->findOneBy(['supplier'=>$supplier,'category'=>$price->getCategory()->getParentid(), 'active'=>1]);
      if ($count==0 and $newsproductsuppliersdiscounts==null) {
        $output->writeln("Agrupo en ".$price->getCategory()->getParentid()->getName()." cuyo id es ".$price->getCategory()->getParentid()->getId());
        $obj=new ERPProductsSuppliersDiscounts();
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
        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();
        if($obj->getEnd()==null) $obj->setShoppingPrices($this->doctrine);
        $this->doctrine->getManager()->clear();
        // delete delete delete
        foreach($categories as $category){
          /*$productsuppliersdiscounts=$repositoryProductsuppliersdiscounts->findOneBy(['supplier'=>$supplier,'category'=>$category, 'active'=>1]);
          if ($productsuppliersdiscounts!=null) {  $output->writeln("Elimino ".$productsuppliersdiscounts->getId());$repositoryProductsuppliersdiscounts->deleteShoppingDiscount($productsuppliersdiscounts->getId());}*/
        }
      }
    }
  }
}

public function updateProducts(InputInterface $input, OutputInterface $output){
  /*  $repository=$this->doctrine->getRepository(ERPProducts::class);
    $products=$repository->findBy(['shoppingPrice'=>0]);
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
    foreach ($products as $product){
      $output->writeln("Cambiando el producto ".$product->getCode());
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProduct.php?product='.$product->getCode());
      $objects=json_decode($json, true);
      if ($objects[0]["class"]==null) continue;
      $object=$objects[0]["class"][0];
      $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
      $supplier=$repositorySuppliers->findOneBy(["code"=>$object["Supplier"]]);
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
      $this->doctrine->getManager()->persist($product);
      $product=$product->calculateIncrementByProduct($this->doctrine);
      $product=$product->calculateCustomerIncrementsByProduct($this->doctrine);
      $this->doctrine->getManager()->persist($product);
      $this->doctrine->getManager()->flush();
      $this->doctrine->getManager()->clear();
      }
  */
  /*
    $repository=$this->doctrine->getRepository(ERPProducts::class);
    $page=5000;
    $totalProducts=round(intval($repository->totalProducts())/$page);
    $count=0;

    while($count<$totalProducts){
        $products=$repository->productsLimit(intval($count*$page),intval($page));
        $count++;
        foreach($products as $id) {
          $product=$repository->findOneBy(["id"=>$id, "company"=>$this->company]);
          $json2=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-clearProducts.php?from='.$product->getCode());
          $movs=json_decode($json2, true);
          $movs=$movs[0];
          // Dejamos de desactivar productos desde el 2/10
          if($movs["class"][0]["movimiento"]!=null)
            if($movs["class"][0]["movimiento"]["date"]>"2019-09-09 00:00:00.000000") $product->setActive(1);
             else {
              $product->setActive(0);
              $product->setDateupd(new \Datetime());
            }
          else {
            $product->setActive(0);
            $product->setDateupd(new \Datetime());
          }
          $this->doctrine->getManager()->persist($product);
          $this->doctrine->getManager()->flush();
          $this->doctrine->getManager()->clear();
        }
    }
    */
  $repository=$this->doctrine->getRepository(ERPProducts::class);
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-clearProducts.php');
  $objects=json_decode($json, true);
  $objects=$objects[0];
  foreach ($objects["class"] as $key=>$object) {
    $product=$repository->findOneBy(["code"=>$object["No."], "company"=>$this->company]);
    if ($product!=null){
      $output->writeln('Desactivando el producto '.$object["No."]);
      $product->setActive(0);
      $product->setDateupd(new \Datetime());
      $this->doctrine->getManager()->persist($product);
      $this->doctrine->getManager()->flush();
      $this->doctrine->getManager()->clear();
    }
  }

}

public function importStock(InputInterface $input, OutputInterface $output, $code=null){
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
  $output->writeln('* Sincronizando stocks....');
  $repositoryStocks=$this->doctrine->getRepository(ERPStocks::class);
  $repositoryStoreLocations=$this->doctrine->getRepository(ERPStoreLocations::class);
  $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
  $repositoryVariants=$this->doctrine->getRepository(ERPVariants::class);
  $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getStocks.php?product='.$code);
  $objects=json_decode($json, true);
  $objects=$objects[0];
    if ($objects){
      foreach ($objects["class"] as $stock){
      $product=$repositoryProducts->findOneBy(["code"=>$stock["code"]]);
      $namenamevariant=$this->variantColor($stock["variant"]);
      $variant=$repositoryVariants->findOneBy(["name"=>$namenamevariant]);
      $storeRepository=$this->doctrine->getRepository(ERPStores::class);
      $store=$storeRepository->findOneBy(["code"=>$stock["almacen"]]);

      if($product) {
            $productVariantId = null;
            $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product,"variant"=>$variant]);
            if($productvariant!=null) {
              $productVariantId=$productvariant->getId();
              $old_stocks=$repositoryStocks->stockVariantUpdate($productvariant->getId(), $stock["almacen"]);
              $output->writeln('El producto '.$product->getId().' tiene la variante '.$stock["variant"]);
            }
            if($old_stocks!=null) {
              if ($old_stocks[0]["id"]!=null){
              $stock_old=$repositoryStocks->findOneBy(["id"=>$old_stocks[0]["id"], "deleted"=>0]);
              $output->writeln('Vamos a actualizar la linea '.$old_stocks[0]["id"].' del producto '.$product->getId().' en el almacen '.$stock["almacen"]);
              if ((int)$stock["stock"]<0) $quantity=0;
              else $quantity=(int)$stock["stock"];
              if ($stock_old->getStorelocation()->getStore()->getManaged()!=1) {
                $updateStocks=$repositoryStocks->setZeroStocks($product->getId(), $store->getId(),$stock_old->getId(),$productVariantId);
                $stock_old->setQuantity(!$quantity?0:$quantity);
                $stock_old->setDateupd(new \Datetime());
                $this->doctrine->getManager()->persist($stock_old);
              }
              }
            }
            else {
              $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["almacen"]]);
              if($location!=null){
              $output->writeln('Vamos a añadir una linea de stock al producto '.$product->getId().' en el almacen '.$stock["almacen"]);
              $obj=new ERPStocks();
              $obj->setCompany($this->company);
              $obj->setDateadd(new \Datetime());
              $obj->setDateupd(new \Datetime());
              $obj->setStoreLocation($location);
              $obj->setProductVariant($productvariant);
              if ((int)$stock["stock"]<0) $quantiy=0;
              else $quantity=(int)$stock["stock"];
              $obj->setQuantity(!$quantity?0:$quantity);
              $obj->setActive(1);
              $obj->setDeleted(0);
              $this->doctrine->getManager()->persist($obj);
            }
            }
            $this->doctrine->getManager()->flush();
            $this->doctrine->getManager()->clear();
          }



    }
    $this->doctrine->getManager()->flush();
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
  $repositoryVariants=$this->doctrine->getRepository(ERPVariants::class);
  $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getStocks.php?from='.$navisionSync->getMaxtimestamp());
  $objects=json_decode($json, true);
  $objects=$objects[0];
    if ($objects){
      foreach ($objects["class"] as $stock){
      $product=$repositoryProducts->findOneBy(["code"=>$stock["code"]]);
      $namenamevariant=$this->variantColor($stock["variant"]);
      $variant=$repositoryVariants->findOneBy(["name"=>$namenamevariant]);
      $storeRepository=$this->doctrine->getRepository(ERPStores::class);
      $store=$storeRepository->findOneBy(["code"=>$stock["almacen"]]);

      if($product) {
            $productVariantId = null;
            $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product,"variant"=>$variant]);
            if($productvariant!=null) {
              $productVariantId=$productvariant->getId();
              $old_stocks=$repositoryStocks->stockVariantUpdate($productvariant->getId(), $stock["almacen"]);
              $output->writeln('El producto '.$product->getId().' tiene la variante '.$stock["variant"]);
            }
            if($old_stocks[0]["id"]!=null) {
              $stock_old=$repositoryStocks->findOneBy(["id"=>$old_stocks[0]["id"], "deleted"=>0]);
              $output->writeln('Vamos a actualizar la linea '.$old_stocks[0]["id"].' del producto '.$product->getId().' en el almacen '.$stock["almacen"]);
              if ((int)$stock["stock"]<0) $quantity=0;
              else $quantity=(int)$stock["stock"];
              if ($stock_old->getStorelocation()->getStore()->getManaged()!=1) {
                $updateStocks=$repositoryStocks->setZeroStocks($product->getId(), $store->getId(),$stock_old->getId(),$productVariantId);
                $stock_old->setQuantity(!$quantity?0:$quantity);
                $stock_old->setDateupd(new \Datetime());
                $this->doctrine->getManager()->persist($stock_old);
              }
            }
            else {
              $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["almacen"]]);
              if($location!=null){
              $obj=new ERPStocks();
              $obj->setCompany($this->company);
              $obj->setDateadd(new \Datetime());
              $obj->setDateupd(new \Datetime());
              $obj->setStoreLocation($location);
              $obj->setProductVariant($productvariant);
              if ((int)$stock["stock"]<0) $quantiy=0;
              else $quantity=(int)$stock["stock"];
              $obj->setQuantity(!$quantity?0:$quantity);
              $obj->setActive(1);
              $obj->setDeleted(0);
              $this->doctrine->getManager()->persist($obj);}
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
  $repositoryVariants=$this->doctrine->getRepository(ERPVariants::class);
  $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
  $repositoryStocks=$this->doctrine->getRepository(ERPStocks::class);
  $storeLocationsRepository=$this->doctrine->getRepository(ERPStoreLocations::class);
  $storeRepository=$this->doctrine->getRepository(ERPStores::class);
  $userRepository=$this->doctrine->getRepository(GlobaleUsers::class);
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getStocksManaged.php?from='.$navisionSync->getMaxtimestamp());
  $objects=json_decode($json, true);
  $objects=$objects[0];
  if ($objects){
    foreach ($objects["class"] as $stock){
      $product=$repositoryProducts->findOneBy(["code"=>$stock["code"]]);
      $namenamevariant=$this->variantColor($stock["variant"]);
      $variant=$repositoryVariants->findOneBy(["name"=>$namenamevariant]);

      if($product) {
            $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product,"variant"=>$variant]);
            if($productvariant!=null) {
              $old_stocks=$repositoryStocks->stockVariantUpdate($productvariant->getId(), $stock["almacen"]);
              $output->writeln('El producto '.$product->getId().' tiene la variante '.$stock["variant"]);
            }
            if($old_stocks[0]["id"]!=null) {
              $stock_old=$repositoryStocks->findOneBy(["id"=>$old_stocks[0]["id"], "deleted"=>0]);
              $output->writeln('Vamos a actualizar la linea '.$old_stocks[0]["id"].' del producto '.$product->getId().' en el almacen '.$stock["almacen"]);
              if ($stock_old->getStorelocation()->getStore()->getManaged()==1) {
              $storeLocation=$storeLocationsRepository->findOneBy(["name"=>$stock["almacen"]]);
              $store=$storeRepository->findOneBy(["code"=>$stock["almacen"]]);
              $user=$userRepository->findOneBy(["email"=>"josemiguel.pardo@ferreteriacampollano.com"]);
              $stockHistory=new ERPStocksHistory();
              $stockHistory->setProductVariant($productvariant);
              $stockHistory->setLocation($storeLocation);
              $stockHistory->setUser($user);
              $stockHistory->setPreviousqty($stock_old->getQuantity());
              $stockHistory->setNewqty($stock_old->getQuantity()+((int)$stock["stock"]));
              $stockHistory->setDateadd(new \Datetime());
              $stockHistory->setDateupd(new \Datetime());
              $stockHistory->setNumOperation($stock["no"]);
              $stockHistory->setQuantity((int)$stock["stock"]);
              $stockHistory->setActive(true);
              $stockHistory->setDeleted(false);
              $this->doctrine->getManager()->persist($stockHistory);
              $stock_old->setQuantity($stock_old->getQuantity()+((int)$stock["stock"]));
              $stock_old->setDateupd(new \Datetime());
              $this->doctrine->getManager()->persist($stock_old);
            }
            }
            else {
              $location=$repositoryStoreLocations->findOneBy(["name"=>$stock["almacen"]]);
              if($location!=null){
              $obj=new ERPStocks();$obj->setCompany($this->company);
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
              $this->doctrine->getManager()->persist($obj);}
            }
            $this->doctrine->getManager()->flush();
            //$this->doctrine->getManager()->clear();
          }
        }

          if ($objects["maxEntry"]!=0) {
            $navisionSync->setMaxtimestamp($objects["maxEntry"]);
            $navisionSync->setLastsync($datetime);
          }
          else {
          $icon=":warning: ";
          $msg=" Fallo en el maxEntry de los almacenes gestinandos";
          //Send notification
          file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$this->discordchannel."&msg=".urlencode($icon."Sincronizacion : ".$msg));
        }
          $this->doctrine->getManager()->persist($navisionSync);
          $this->doctrine->getManager()->flush();
    }



}

public function importProductsSuppliers(InputInterface $input, OutputInterface $output) {
  $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
  $repositoryProductsSuppliers=$this->doctrine->getRepository(ERPProductsSuppliers::class);
  $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
  $page=5000;
  $totalProducts=round(intval($repositoryProducts->totalProducts())/$page);
  $count=0;

  while($count<$totalProducts){
    $products=$repositoryProducts->productsLimit(intval($count*$page),intval($page));
    $count++;
    foreach($products as $id) {
      $product=$repositoryProducts->findOneBy(["id"=>$id, "company"=>$this->company]);
      $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product, "variant"=>null]);
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getSuppliersByProduct.php?product='.$product->getCode());
      $objects=json_decode($json, true);
      $objects=$objects[0];
      foreach ($objects["class"] as $supplierCode){
          if ($supplierCode["No."]==null) continue;
          $supplier=$repositorySuppliers->findOneBy(["code"=>$supplierCode["No."], "company"=>$this->company, "active"=>1, "deleted"=>0]);
          if ($supplier==null) continue;
          $productsSuppliers=$repositoryProductsSuppliers->findOneBy(["productvariant"=>$productvariant, "supplier"=>$supplier]);
          if ($productsSuppliers==null) {
            $output->writeln("Añadiendo el proveedor ".$supplier->getCode()." al producto ".$product->getCode());
            $obj=new ERPProductsSuppliers();
            $obj->setProductVariant($productvariant);
            $obj->setSupplier($supplier);
            $obj->setCompany($this->company);
            $obj->setActive(1);
            $obj->setDeleted(0);
            $obj->setDateadd(new \Datetime());
            $obj->setDateupd(new \Datetime());
            $this->doctrine->getManager()->persist($obj);
            $this->doctrine->getManager()->flush();
          }
      }
      $this->doctrine->getManager()->clear();
    }
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

  $repositoryCategories=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryCustomerGroups=$this->doctrine->getRepository(ERPCustomerGroups::class);
  $customergroups = [];
  $customergroups[1] = $repositoryCustomerGroups->find(1);
  $customergroups[2] = $repositoryCustomerGroups->find(2);
  $customergroups[3] = $repositoryCustomerGroups->find(3);
  $repositoryIncrements=$this->doctrine->getRepository(ERPIncrements::class);

  //------   Critical Section START   ------
  $fpp = null;
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fpp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-getproductsprices.csv', 'w');
  } else {
      $fpp = fopen('/tmp/axiom-getproductsprices.csv', 'w');
  }
  $output->writeln('* Sincronizando Incrementos....');
  $output->writeln(' - Obteniendo Categorias');
  $query="SELECT DISTINCT(p.category_id) AS category_id, c.name as category_name FROM erpproducts p LEFT JOIN erpcategories c on p.category_id=c.id WHERE category_id<>'' and p.company_id='2'";
  $params=[];
  $categories = $this->doctrine->getManager()->getConnection()->executeQuery($query, $params)->fetchAll();
  // Para cada categoria
  for($i=0; $i<count($categories); $i++){
    $category_id    = $categories[$i]["category_id"];
    $category_name  = $categories[$i]["category_name"];
    $category       = $repositoryCategories->find($category_id);
    $output->writeln(' - Categoría - '.$category_id.' - '.$category_name);
    $query="SELECT code FROM erpproducts WHERE category_id='".$category_id."'";
    $params=[];
    $products = $this->doctrine->getManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    if ($products!=null){
      $postdata = http_build_query(
        array(
                'products' => json_encode($products)
            )
      );
      $opts = array('http' =>
        array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postdata
        )
      );
      $context = stream_context_create($opts);
      // Para cada producto obtiene por proveedor y grupo su price de compra y su incremento
      $products_prices = file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProductsPrices.php',false,$context);
      if ($products_prices!=null){
        $aproducts_prices=json_decode($products_prices, true);
        if (isset($aproducts_prices['products_prices']))
          foreach($aproducts_prices['products_prices'] as $product=>$groups){
            foreach($groups as $group=>$increment){
              foreach($increment as $vendor=>$value){
                fwrite($fpp, '"'.$category_name.'";"'.$category_id.'";"'.$product.'";"'.$group.'";"'.$vendor.'";"'.str_replace('.',',',$value).'"'.PHP_EOL);
              }
            }
          }
        if (isset($aproducts_prices['increments'])){
          $increments = $aproducts_prices['increments'];
          // Obtener proveedores de la categoría
          $query="SELECT DISTINCT(ps.supplier_id) as supplier_id, s.code as supplier_code FROM erpproducts_suppliers ps left join erpproducts_variants pv on pv.id=ps.productvariant_id left join erpproducts p on p.id=pv.product_id LEFT JOIN erpsuppliers s on s.id=ps.supplier_id WHERE p.id in (SELECT id FROM erpproducts WHERE category_id='".$category_id."')";
          $params=[];
          $suppliers = $this->doctrine->getManager()->getConnection()->executeQuery($query, $params)->fetchAll();
          for($j=0; $j<count($suppliers); $j++){
            // Para cada proveedor/grupo de la categoría se almacena su incremento en el producto
            $supplier_id    = $suppliers[$j]['supplier_id'];
            $supplier_code  = $suppliers[$j]['supplier_code'];
            $supplier       = $repositorySuppliers->find($supplier_id);
            $increment = null;
            if (isset($increments[$supplier_code]))
              $increment = $increments[$supplier_code];
            if ($increment!=null){
              for($k=1; $k<4; $k++){
                // Existe el incremento
                $oincrement=$repositoryIncrements->findOneBy(["category"=>$category, "supplier"=>$supplier, "customergroup"=>$customergroups[$k], "company"=>$this->company, "deleted"=>0]);
                if ($oincrement==null){
                  $oincrement=new ERPIncrements();
                  $oincrement->setCategory($category);
                  $oincrement->setSupplier($supplier);
                  $oincrement->setCustomerGroup($customergroups[$k]);
                  $oincrement->setCompany($this->company);
                  $oincrement->setDeleted(0);
                  $oincrement->setDateadd(new \Datetime());
                }
                $oincrement->setActive(1);
                $oincrement->setIncrement($increment[$k]);
                $oincrement->setDateupd(new \Datetime());
                try{
                  $this->doctrine->getManager()->persist($oincrement);
                  $this->doctrine->getManager()->flush();
                  // Actualizar los productos de la categoría
                  //$oincrement->calculateIncrementsBySupplierCategory($this->doctrine);
                }catch(Exception $e){
                  $output->writeln(' - Error - '.$category_id.' - '.$category_name);
                }
              }
            }
          }
        }
      }
    }
  }
  $this->doctrine->getManager()->clear();
  fclose($fpp);
  //------   Critical Section END   ------
  //------   Remove Lock Mutex    ------
  fclose($fp);
}

public function importIncrementsCustomers(InputInterface $input, OutputInterface $output) {
  //------   Create Lock Mutex    ------
  //$fp = fopen('/tmp/axiom-navisionGetProducts-importIncrements.lock', 'c');
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetProducts-importIncrementsCustomers.lock', 'c');
  } else {
      $fp = fopen('/tmp/axiom-navisionGetProducts-importIncrementsCustomers.lock', 'c');
  }
  if (!flock($fp, LOCK_EX | LOCK_NB)) {
    $output->writeln('* Fallo al iniciar la sincronizacion incrementos: El proceso ya esta en ejecución.');
    exit;
  }
  $fpp = null;
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $fpp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-getproductspricescustomers.csv', 'w');
  } else {
      $fpp = fopen('/tmp/axiom-getproductspricescustomers.csv', 'w');
  }

  $repositoryCategories=$this->doctrine->getRepository(ERPCategories::class);
  $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
  $repositoryCustomerIncrements=$this->doctrine->getRepository(ERPCustomerIncrements::class);

  $output->writeln('* Sincronizando incrementos de clientes ....');
  $output->writeln(' - Obteniendo Categorias');
  $query="SELECT DISTINCT(p.category_id) AS category_id, c.name as category_name FROM erpproducts p LEFT JOIN erpcategories c on p.category_id=c.id WHERE category_id<>'' and p.company_id='2'";
  $params=[];
  $categories = $this->doctrine->getManager()->getConnection()->executeQuery($query, $params)->fetchAll();
  // Para cada categoria
  for($i=0; $i<count($categories); $i++){
    $category_id    = $categories[$i]["category_id"];
    $category_name  = $categories[$i]["category_name"];
    $category       = $repositoryCategories->find($category_id);
    $output->writeln(' - Categoría - '.$category_id.' - '.$category_name);
    $query="SELECT code FROM erpproducts WHERE category_id='".$category_id."'";
    $params=[];
    $products = $this->doctrine->getManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    if ($products!=null){
      $postdata = http_build_query(
        array(
                'products' => json_encode($products)
            )
      );
      $opts = array('http' =>
        array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postdata
        )
      );
      $context = stream_context_create($opts);
      // Para cada producto obtiene por proveedor y grupo su price de compra y su incremento
      $products_prices = file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProductsPricesCustomers.php',false,$context);
      if ($products_prices!=null){
        $aproducts_prices=json_decode($products_prices, true);
        if (isset($aproducts_prices['products_prices']))
          foreach($aproducts_prices['products_prices'] as $product=>$increment){
            foreach($increment as $customer=>$value){
              fwrite($fpp, '"'.$category_name.'";"'.$category_id.'";"'.$product.'";"'.$customer.'";"'.str_replace('.',',',$value['increment']).'"'.PHP_EOL);
            }
          }
        if (isset($aproducts_prices['increments']) && count($aproducts_prices['increments'])>0){
          $increments = $aproducts_prices['increments'];
          // Obtener proveedores de la categoría
          $query="SELECT DISTINCT(ps.supplier_id) as supplier_id, s.code as supplier_code FROM erpproducts_suppliers ps left join erpproducts_variants pv on pv.id=ps.productvariant_id left join erpproducts p on p.id=pv.product_id LEFT JOIN erpsuppliers s on s.id=ps.supplier_id WHERE p.id in (SELECT id FROM erpproducts WHERE category_id='".$category_id."')";
          $params=[];
          $suppliers = $this->doctrine->getManager()->getConnection()->executeQuery($query, $params)->fetchAll();
          for($j=0; $j<count($suppliers); $j++){
            // Para cada proveedor/cliente de la categoría se almacena su incremento
            $supplier_id    = $suppliers[$j]['supplier_id'];
            $supplier       = $repositorySuppliers->find($supplier_id);
            foreach ($increments as $customer_code=>$increment){
              $customer = $repositoryCustomers->findOneBy(["code"=>$customer_code]);
              if ($customer!=null && $increment!=null && count($increment)>0){
                // Existe el incremento
                $oincrementcustomers=$repositoryCustomerIncrements->findOneBy(["category"=>$category, "supplier"=>$supplier, "customer"=>$customer, "company"=>$this->company, "deleted"=>0]);
                if ($oincrementcustomers==null){
                  $oincrementcustomers=new ERPCustomerIncrements();
                  $oincrementcustomers->setCategory($category);
                  $oincrementcustomers->setSupplier($supplier);
                  $oincrementcustomers->setCustomer($customer);
                  $oincrementcustomers->setCompany($this->company);
                  $oincrementcustomers->setDeleted(0);
                  $oincrementcustomers->setDateadd(new \Datetime());
                }
                $oincrementcustomers->setActive(1);
                $oincrementcustomers->setIncrement($increment['increment']);
                $oincrementcustomers->setDateupd(new \Datetime());

                $oincrementcustomers->setStart(($increment['start']=='1753-01-01 12:00:00.000000'?null:date_create_from_format("Y-m-d h:i:s.u",$increment['start'])));
                $oincrementcustomers->setEnd(($increment['end']=='1753-01-01 12:00:00.000000'?null:date_create_from_format("Y-m-d h:i:s.u",$increment['end'])));
                try{
                  $this->doctrine->getManager()->persist($oincrementcustomers);
                  $this->doctrine->getManager()->flush();
                  // Actualizar los productos de la categoría
                  // $oincrementcustomers->calculateIncrementsBySupplierCategory($this->doctrine);
                }catch(Exception $e){
                  $output->writeln(' - Error - '.$category_id.' - '.$category_name);
                }
              }
            }
          }
        }
      }
    }
  }
  $this->doctrine->getManager()->clear();
  fclose($fpp);

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
              $obj->setCompany($this->company);
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
              $obj->setCompany($this->company);
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
  $repository=$this->doctrine->getRepository(ERPVariants::class);
  $output->writeln('* Importando variantes....');
  $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
  $repositoryVariantTypes=$this->doctrine->getRepository(ERPVariantsTypes::class);
  $variantstypes=$repositoryVariantTypes->findAll();
  foreach ($variantstypes as $varianttype){
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getVariants.php?variant='.$varianttype->getName());
      $output->writeln('        -Importando valores de la variante '.$varianttype->getName());
      $objects=json_decode($json, true);
      $objects=$objects[0]["class"];
      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
      foreach ($objects as $object){
        $variant;
        if ($varianttype->getName()=="Color") $variant=$this->variantColor($object["value"]);
        else if ($varianttype->getName()=="Fragancia") $variant=$this->variantFragrance($object["value"]);
        else $variant=$object["value"];

        $obj=$repository->findOneBy(["name"=>$variant]);
        if ($obj==null){
          $obj=new ERPVariants();
          $obj->setVariantType($varianttype);
          $obj->setName($variant);
          $obj->setDateadd(new \Datetime());
          $obj->setDateupd(new \Datetime());
          $obj->setDeleted(0);
          $obj->setActive(1);
        }
        $this->doctrine->getManager()->persist($obj);
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
  $repositoryVariantTypes=$this->doctrine->getRepository(ERPVariantsTypes::class);
  $variantstypes=$repositoryVariantTypes->findAll();
  foreach($variantstypes as $varianttype){
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProductsVariants.php?variant='.$varianttype->getName());
      $objects=json_decode($json, true);
      $objects=$objects[0]["class"];
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
      $output->writeln('* Importando productos agrupados por '.$varianttype->getName());
      foreach ($objects as $object){
        $repositoryProduct=$this->doctrine->getRepository(ERPProducts::class);
        $product=$repositoryProduct->findOneBy(["code"=>$object["product"]]);
        $repositoryvariant=$this->doctrine->getRepository(ERPVariants::class);
        $namevariant;
        //$output->writeln('       - Añadiendo variante a '.$object["product"]);
        if ($varianttype->getName()=="Color") $namevariant=$this->variantColor($object["Code"]);
        else if ($varianttype->getName()=="Fragancia") $namevariant=$this->variantFragrance($object["Code"]);
        else $namevariant=$object["Code"];


        $variant=$repositoryvariant->findOneBy(["varianttype"=>$varianttype, "name"=>$namevariant]);
        $obj=$repository->findOneBy(["variant"=>$variant, "product"=>$product]);
        if ($obj==null and $product!=null){
            $output->writeln('* Asignando la variante '.$object["Code"].' al producto '.$object["product"]);
            if ($product->getGrouped()==0) $product->setGrouped(1);
            $obj=new ERPProductsVariants();
            $obj->setProduct($product);
            $obj->setVariant($variant);
            $obj->setDateadd(new \Datetime());
            $obj->setDateupd(new \Datetime());
            $obj->setDeleted(0);
            $obj->setActive(1);
            $this->doctrine->getManager()->persist($obj);
            $this->doctrine->getManager()->persist($product);
        }

          $this->doctrine->getManager()->flush();
          $this->doctrine->getManager()->clear();
        }
      }
      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
}

public function variantFragrance($namevariant){
  if ($namevariant=="BLUE SILVE") $namevariant="Blue Silver";
  if ($namevariant=="FOREVER YO") $namevariant="Forever Young";
  if ($namevariant=="NEUTRALIZA") $namevariant="Neutralizador";
  else $namevariant=ucwords(strtolower($namevariant));
  return $namevariant;
}

public function variantColor($namevariant){
  if ($namevariant=="AMARILLO C") $namevariant="Amarillo Claro";
  else if ($namevariant=="AMARILLO F") $namevariant="Amarillo Fluor";
  else if ($namevariant=="AMARILLO L") $namevariant="Amarillo Limon";
  else if ($namevariant=="AMARILLO R") $namevariant="Amarillo Real";
  else if ($namevariant=="ARENA VIGO") $namevariant="Arena Vigore";
  else if ($namevariant=="AZUL COBAL") $namevariant="Azul Cobalto";
  else if ($namevariant=="AZUL LUMIN") $namevariant="Azul Luminoso";
  else if ($namevariant=="AZUL MARIN") $namevariant="Azul Marino";
  else if ($namevariant=="AZUL ULTA") $namevariant="Azul Ultramar";
  else if ($namevariant=="BEIGE 585" or $namevariant=="BEIGE") $namevariant="Beige";
  else if ($namevariant=="BLANCO 501" or $namevariant=="BLANCO" or $namevariant=="BLANCA") $namevariant="Blanco";
  else if ($namevariant=="BLANCO BRI") $namevariant="Blanco Brillo";
  else if ($namevariant=="BLANCOPERL") $namevariant="Blanco Perla";
  else if ($namevariant=="CREMA 586" or $namevariant=="CREMA") $namevariant="Crema";
  else if ($namevariant=="GAMUZA 543" or $namevariant=="GAMUZA") $namevariant="Gamuza";
  else if ($namevariant=="GRIS AZULA") $namevariant="Gris Azulado";
  else if ($namevariant=="GRIS OSCUR") $namevariant="Gris Oscuro";
  else if ($namevariant=="GRIS VIGOR") $namevariant="Gris Vigore";
  else if ($namevariant=="MALVA MAST") $namevariant="Malva Master";
  else if ($namevariant=="MARFIL 528" or $namevariant=="MARFIL") $namevariant="Marfil";
  else if ($namevariant=="MARRON TAB") $namevariant="Marron Tabaco";
  else if ($namevariant=="MARRONVINT") $namevariant="Marron Vintage";
  else if ($namevariant=="NARANJA CL") $namevariant="Naranja Claro";
  else if ($namevariant=="NARANJA FL") $namevariant="Naranja Fluor";
  else if ($namevariant=="NEGRO 567" or $namevariant=="NEGRO") $namevariant="Negro";
  else if ($namevariant=="VERDE CARR") $namevariant="Verde Carruajes";
  else if ($namevariant=="NEGRO BRIL") $namevariant="Negro Brillo";
  else if ($namevariant=="OCRE" or $namevariant=="OCRE 587") $namevariant="Ocre";
  else if ($namevariant=="PARDO" or $namevariant=="PARDO 517") $namevariant="Pardo";
  else if ($namevariant=="RAYAS GRAN") $namevariant="Rayas Granate";
  else if ($namevariant=="RAYAS NEGR") $namevariant="Rayas Negras";
  else if ($namevariant=="ROJO BURDE") $namevariant="Rojo Burdeos";
  else if ($namevariant=="ROJO CARRU") $namevariant="Rojo Carruaje";
  else if ($namevariant=="ROJO INGLE") $namevariant="Rojo Ingles";
  else if ($namevariant=="ROJOIMPERI") $namevariant="Rojo Imperial";
  else if ($namevariant=="ROSA PALID") $namevariant="Rosa Palido";
  else if ($namevariant=="SALMON OSC") $namevariant="Salmon Oscuro";
  else if ($namevariant=="TURQUESA C") $namevariant="Turquesa Claro";
  else if ($namevariant=="VERDE BOSQ") $namevariant="Verde Bosque";
  else if ($namevariant=="VERDE CLAR") $namevariant="Verde Claro";
  else if ($namevariant=="VERDE FRON") $namevariant="Verde Fronton";
  else if ($namevariant=="VERDE HIER") $namevariant="Verde Hierba";
  else if ($namevariant=="VERDE PIST") $namevariant="Verde Pistacho";
  else if ($namevariant=="VERDE PRIM") $namevariant="Verde Primavera";
  else if ($namevariant=="VINTAGE RO") $namevariant="Vintage Rose";
  else if ($namevariant=="NEGRO/BLN") $namevariant="Negro y blanco";
  else if ($namevariant=="ROJO/BLN") $namevariant="Rojo y blanco";
  else if ($namevariant=="ROYAL/BLN") $namevariant="Azul royal y blanco";
  else $namevariant=ucwords(strtolower($namevariant));
  return $namevariant;
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
    $this->doctrine->getManager()->persist($product);
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
  $output->writeln('* Sincronizando References....');
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetReferences.php');
  $objects=json_decode($json, true);

  $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
  $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
  $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
  $repositoryVariants=$this->doctrine->getRepository(ERPVariants::class);
  $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
  $repositoryReferences=$this->doctrine->getRepository(ERPReferences::class);

  //Disable SQL logger
  $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
  $deleteReferencesChange = [];
  foreach ($objects as $key=>$object){
     // Si no hay cambio no se hace nada
     if ($object['codigo_antiguo']!=$object['codigo_nuevo']){
       $action     = $object['accion'];
       $adatos_old = explode('~',$object['codigo_antiguo']);
       $code_old   = null;
       $product_old = null;
       $variant_old = null;
       $type_old   = null;
       $supplier_customer_old   = null;
       if (count($adatos_old)>=4){
          $code_old   = $adatos_old[0];
          $product_old = $adatos_old[1];
          $type_old   = $adatos_old[2];
          $supplier_customer_old = $adatos_old[3];
       }
       if (count($adatos_old)==5){
          $variant_old = $adatos_old[4];
       }
       $adatos_new = explode('~',$object['codigo_nuevo']);
       $code_new   = null;
       $product_new = null;
       $variant_new = null;
       $type_new   = null;
       $supplier_customer_new   = null;
       if (count($adatos_new)>=4){
          $code_new   = $adatos_new[0];
          $product_new = $adatos_new[1];
          $type_new   = $adatos_new[2];
          $supplier_customer_new = $adatos_new[3];
       }
       if (count($adatos_new)==5){
          $variant_new = $adatos_new[4];
       }
       $references  = null;
       if (isset($object['references']))
         $references=$object['references'];
       $oreferences = null;

       // Borrado de Reference
       if ($action=='D') {
         $product=$repositoryProducts->findOneBy(["code"=>$product_old]);
         $ovariant=null;
         if ($variant_old!='')
          $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_old]);
         $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);

         if ($oproductvariant!=null){
           if ($type_old=='1'){
             $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_old]);
             $oreferences=$repositoryReferences->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "customer"=>$customer, "supplier"=>null]);
           }
           else
           if ($type_old=='2') {
             $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_old]);
             $oreferences=$repositoryReferences->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>$supplier]);
           }
           else
               $oreferences=$repositoryReferences->findOneBy(["name"=>$code_old, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>null]);

           if ($oreferences!=null){
             $output->writeln($action.' - '.$code_old);
             $this->doctrine->getManager()->remove($oreferences);
             $this->doctrine->getManager()->flush();
             $this->doctrine->getManager()->clear();
           }
         }
       }else{
         $code_id               = null;
         $product_id            = null;
         $variant_id            = null;
         $type_id               = null;
         $supplier_customer_id  = null;
         if ($action=='I'){
           $code_id               = $code_new;
           $product_id            = $product_new;
           $variant_id            = $variant_new;
           $type_id               = $type_new;
           $supplier_customer_id  = $supplier_customer_new;
         }else
         if ($action=='U'){
           $code_id               = $code_old;
           $product_id            = $product_old;
           $variant_id            = $variant_old;
           $type_id               = $type_old;
           $supplier_customer_id  = $supplier_customer_old;
         }
         $oproduct=$repositoryProducts->findOneBy(["code"=>$product_id]);
         $ovariant = null;
         if ($variant_id!=null)
          $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_id]);
         $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);

         if ($oproductvariant!=null) {
            $supplier  = null;
            $customer = null;
            if ($type_id==1){
              $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_id]);
              $oreferences=$repositoryReferences->findOneBy(["name"=>$code_id, "productvariant"=>$oproductvariant, "customer"=>$customer, "supplier"=>null, "type"=>2]);
              if ($oreferences==null){
                $oreferences=$repositoryReferences->findOneBy(["name"=>$code_id, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>null, "type"=>2]);
              }
            }else
            if ($type_id==2){
              $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_id]);
              $oreferences=$repositoryReferences->findOneBy(["name"=>$code_id, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>$supplier, "type"=>1]);
              if ($oreferences==null){
                $oreferences=$repositoryReferences->findOneBy(["name"=>$code_id, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>null, "type"=>1]);
              }
            }

            if ($oreferences==null){
              if ($type_id==1)
                $output->writeln('  - Añadiendo al producto '.$product_new.' la referencia '.$code_new.' del cliente '.$supplier_customer_id);
              else
                $output->writeln('  - Añadiendo al producto '.$product_new.' la referencia '.$code_new.' del proveedor '.$supplier_customer_id);
              $oreferences=new ERPReferences();
              $oreferences->setDateadd(new \Datetime());
            }else {
              if ($type_id==1)
                $output->writeln('  - Modificando al producto '.$product_new.' la referencia '.$code_new.' del cliente '.$supplier_customer_id);
              else
                $output->writeln('  - Modificando al producto '.$product_new.' la referencia '.$code_new.' del proveedor '.$supplier_customer_id);
            }
            // Si es modificación hay que obtener los nuevos datos
            if ($action=='U'){
              $customer = null;
              $suppliers = null;
              $oproduct=$repositoryProducts->findOneBy(["code"=>$product_new]);
              $ovariant = null;
              if ($variant_id!=null)
               $ovariant=$repositoryVariants->findOneBy(["name"=>$variant_new]);
              $oproductvariant = $repositoryProductsVariants->findOneBy(["product"=>$oproduct, "variant"=>$ovariant]);
              if ($oproductvariant!=null) {
                 if ($type_new==1){
                   $customer=$repositoryCustomers->findOneBy(["code"=>$supplier_customer_new]);
                   if ($oreferences==null){
                     $oreferences=$repositoryReferences->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "customer"=>$customer, "supplier"=>null, "type"=>2]);
                     if ($oreferences==null){
                       $oreferences=$repositoryReferences->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>null, "type"=>2]);
                     }
                   }
                 }else
                 if ($type_new==2){
                   $supplier=$repositorySuppliers->findOneBy(["code"=>$supplier_customer_new]);
                   if ($oreferences==null){
                     $oreferences=$repositoryReferences->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>$supplier, "type"=>1]);
                     if ($oreferences==null){
                       $oreferences=$repositoryReferences->findOneBy(["name"=>$code_new, "productvariant"=>$oproductvariant, "customer"=>null, "supplier"=>null, "type"=>1]);
                     }
                   }
                 }
              }
              if ($oreferences==null){
                $oreferences=new ERPReferences();
                $oreferences->setDateadd(new \Datetime());
              }
            }
            if ($oproductvariant!=null){
              $oreferences->setName($code_new);
              if (isset($references["Description"]))
                $oreferences->setDescription($references["Description"]);
              else
                $oreferences->setDescription("");
              $oreferences->setDateupd(new \Datetime());
              $oreferences->setProductvariant($oproductvariant);
              $oreferences->setDeleted(0);
              $oreferences->setActive(1);
              if ($type_new==2){
                $oreferences->setType(1);
              } else if ($type_new==1){
                $oreferences->setType(2);
              }
              $oreferences->setSupplier($supplier);
              $oreferences->setCustomer($customer);
              $this->doctrine->getManager()->persist($oreferences);
              $this->doctrine->getManager()->flush();
              $this->doctrine->getManager()->clear();
            }
          }
        }
      }
      // Sumar Reference al json para eliminar en tabla de cambios
      if (isset($object['references']))
        unset($object['references']);
      $deleteReferencesChange[] = $object;
    }
    // Eliminado de tabla de cambios
    $output->writeln('Eliminar cambios realizados....');
    $postdata = http_build_query(
        array(
            'deleteReferencesChange' => json_encode($deleteReferencesChange)
        )
    );
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context = stream_context_create($opts);
    file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-changeGetReferencesDelete.php',false,$context);
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
        $reference=$repository->findOneBy(["id"=>$id, "company"=>$this->company]);
        if ($reference->getType()==1)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getReference.php?reference='.$reference->getName().'$crossReferenceNo='.$reference->getSupplier()->getCode());
        else if ($reference->getType()==2)
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getReference.php?reference='.$reference->getName().'$crossReferenceNo='.$reference->getCustomer()->getCode());

        $objects=json_decode($json, true);
        if ($objects[0]["class"]!=null) continue;
        $output->writeln('* Desactivando la referencia  '.$reference->getName());

        $reference->setActive(0);
        $reference->setDeleted(1);
        $this->doctrine->getManager()->persist($reference);
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
      $this->doctrine->getManager()->persist($product);
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
      $this->doctrine->getManager()->persist($product);
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
        $product=$repository->findOneBy(["id"=>$id, "company"=>$this->company]);
        $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getProduct.php?product='.$product->getCode());
        $objects=json_decode($json, true);
        if ($objects[0]["class"]!=null) continue;
        $output->writeln('* Desactivando el producto '.$product->getCode());
        $repository->deleteRelations($product->getId());
        $product->setActive(0);
        $product->setDeleted(1);
        $this->doctrine->getManager()->persist($product);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();
      }

    }

  }
}
?>
