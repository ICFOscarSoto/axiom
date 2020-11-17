<?php
namespace App\Modules\Prestashop\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPDepartments;
use App\Modules\ERP\Entity\ERPContacts;
use App\Modules\ERP\Entity\ERPAddresses;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPPaymentTerms;
use App\Modules\ERP\Entity\ERPCustomerActivities;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;
use App\Modules\ERP\Entity\ERPCustomerOrdersData;
use App\Modules\ERP\Entity\ERPCustomerCommercialTerms;
use App\Modules\Carrier\Entity\CarrierCarriers;
use App\Modules\Carrier\Entity\CarrierShippingConditions;
use App\Modules\ERP\Entity\ERPBankAccounts;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class PrestashopGetProducts extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="https://www.ferreteriacampollano.com";
  private $token="6TI5549NR221TXMGMLLEHKENMG89C8YV";

  protected function configure(){
        $this
            ->setName('prestashop:getproducts')
            ->setDescription('Sync prestashop principal entities')
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
      case 'products': $this->importProducts($input, $output);
      break;
      case 'images': $this->importImages($input, $output);
      break;
      case 'web': $this->importWebFields($input, $output);
      break;
      case 'updateproduct': $this->updateProduct($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importProducts(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
         $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-prestashopGetProducts-importProducts.lock', 'c');
     } else {
        $fp = fopen('/tmp/axiom-prestashopGetProducts-importProducts.lock', 'c');
     }

     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de productos con prestashop: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $rawSync=false;
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $productRepository=$this->doctrine->getRepository(ERPProducts::class);
     $companyRepository=$this->doctrine->getRepository(GlobaleCompanies::class);
     $company=$companyRepository->findOneBy(["id"=>2]);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:products"]);
     $doctrine = $this->getContainer()->get('doctrine');
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
       $navisionSync->setLastsync(date_create_from_format("Y-m-d H:i:s","2000-01-01 00:00:00"));
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando productos prestashop....');

     $auth = base64_encode($this->token);
     $context = stream_context_create([
         "http" => ["header" => "Authorization: Basic $auth"]
     ]);
     $array=[];
     $products=[];
     try{
           $xml_string=file_get_contents($this->url."/api/products/?display=[reference,name,description]&filter[date_upd]=>[".$navisionSync->getLastsync()->format("Y-m-d H:i:s")."]&date=1", false, $context);
           $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
           $json = json_encode($xml);
           $array = json_decode($json,TRUE);
           $products=isset($array["products"]["product"])?$array["products"]["product"]:[];

       foreach($products as $prodPrestashop){
         $output->writeln("Actualizando producto: ".$prodPrestashop["reference"]." - ".$prodPrestashop["name"]["language"]);
         $description=is_array($prodPrestashop["description"]["language"])?"":$prodPrestashop["description"]["language"];
         $product=$productRepository->findOneBy(["company"=>$company, "code"=>$prodPrestashop["reference"], "deleted"=>0]);
         if($product){
           $product->setDescription($description);
           $doctrine->getManager()->persist($product);
           $doctrine->getManager()->flush();
         }
         $doctrine->getManager()->clear();
       }

     }catch(Exception $e){}

     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:products"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("prestashop:products");
     }

     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($datetime->getTimestamp());
     $doctrine->getManager()->persist($navisionSync);
     $doctrine->getManager()->flush();
     //------   Critical Section END   ------
     //------   Remove Lock Mutex    ------
     fclose($fp);
   }


   public function importImages(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
         $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-prestashopGetProducts-importImages.lock', 'c');
     } else {
        $fp = fopen('/tmp/axiom-prestashopGetProducts-importImages.lock', 'c');
     }

     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de imagenes de productos con prestashop: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $rawSync=false;
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $productRepository=$this->doctrine->getRepository(ERPProducts::class);
     $companyRepository=$this->doctrine->getRepository(GlobaleCompanies::class);
     $company=$companyRepository->findOneBy(["id"=>2]);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:products_images"]);
     $doctrine = $this->getContainer()->get('doctrine');
     $path=$this->getContainer()->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$company->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'products'.DIRECTORY_SEPARATOR;
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
       $navisionSync->setLastsync(date_create_from_format("Y-m-d H:i:s","2000-01-01 00:00:00"));
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando imagenes de productos prestashop....');

     $auth = base64_encode($this->token);
     $context = stream_context_create([
         "http" => ["header" => "Authorization: Basic $auth"]
     ]);
     $array=[];
     $products=[];
     try{
           $xml_string=file_get_contents($this->url."/api/products/?display=[id,reference,id_default_image]&filter[date_upd]=>[".$navisionSync->getLastsync()->format("Y-m-d H:i:s")."]&date=1", false, $context);
           $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
           $json = json_encode($xml);
           $array = json_decode($json,TRUE);
           $products=isset($array["products"]["product"])?$array["products"]["product"]:[];

       foreach($products as $prodPrestashop){
         $output->writeln("Actualizando imagenes de producto: ".$prodPrestashop["reference"]);

         $product=$productRepository->findOneBy(["company"=>$company, "code"=>$prodPrestashop["reference"], "deleted"=>0]);
         if($product && isset($prodPrestashop["id_default_image"]) && !is_array($prodPrestashop["id_default_image"])){
           $imgurl=$this->url."/api/images/products/".$prodPrestashop["id"]."/".$prodPrestashop["id_default_image"]."/";

           //Get default image
           file_put_contents($path.$product->getId()."-large.png", file_get_contents($imgurl, false, $context));
           file_put_contents($path.$product->getId()."-thumb.png", file_get_contents($imgurl.'medium_default', false, $context));
           file_put_contents($path.$product->getId()."-small.png", file_get_contents($imgurl.'medium_default', false, $context));
           file_put_contents($path.$product->getId()."-medium.png", file_get_contents($imgurl.'large_default', false, $context));

           //Check for secondary images
           try{
                 $xml_string=file_get_contents($this->url."/api/images/products/".$prodPrestashop["id"], false, $context);
                 $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
                 $json = json_encode($xml);
                 $array = json_decode($json,TRUE);
                 $images=isset($array["image"]["declination"])?$array["image"]["declination"]:[];
                 $i=1;
                 foreach($images as $key=>$prodImage){

                   if(isset($prodImage["@attributes"]["id"]) && $prodImage["@attributes"]["id"]!=$prodPrestashop["id_default_image"]){
                     if (!file_exists($path.$product->getId())) {
                         mkdir($path.$product->getId(), 0777, true);
                     }
                     $imgurl=$this->url."/api/images/products/".$prodPrestashop["id"]."/".$prodImage["@attributes"]["id"]."/";
                     file_put_contents($path.$product->getId().DIRECTORY_SEPARATOR.$product->getId()."-".$i."-large.png", file_get_contents($imgurl, false, $context));
                     file_put_contents($path.$product->getId().DIRECTORY_SEPARATOR.$product->getId()."-".$i."-thumb.png", file_get_contents($imgurl.'medium_default', false, $context));
                     file_put_contents($path.$product->getId().DIRECTORY_SEPARATOR.$product->getId()."-".$i."-small.png", file_get_contents($imgurl.'medium_default', false, $context));
                     file_put_contents($path.$product->getId().DIRECTORY_SEPARATOR.$product->getId()."-".$i."-medium.png", file_get_contents($imgurl.'large_default', false, $context));
                     $i++;
                   }
                 }

          }catch(Exception $e){}

         }
         $doctrine->getManager()->clear();
       }

     }catch(Exception $e){}

     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:products_images"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("prestashop:products_images");
     }

     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($datetime->getTimestamp());
     $doctrine->getManager()->persist($navisionSync);
     $doctrine->getManager()->flush();
     //------   Critical Section END   ------
     //------   Remove Lock Mutex    ------
     fclose($fp);
   }

   public function importWebFields(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
         $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-prestashopGetProducts-importWebFields.lock', 'c');
     } else {
        $fp = fopen('/tmp/axiom-prestashopGetProducts-importWebFields.lock', 'c');
     }

     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de productos con prestashop: El proceso ya esta en ejecución.');
       exit;
     }


     //------   Critical Section START   ------
     $rawSync=false;
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $productRepository=$this->doctrine->getRepository(ERPProducts::class);
     $WebProductRepository=$this->doctrine->getRepository(ERPWebProducts::class);
     $companyRepository=$this->doctrine->getRepository(GlobaleCompanies::class);
     $company=$companyRepository->findOneBy(["id"=>2]);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:webfields"]);
     $doctrine = $this->getContainer()->get('doctrine');
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
       $navisionSync->setLastsync(date_create_from_format("Y-m-d H:i:s","2000-01-01 00:00:00"));
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando campos web de prestashop....');

     $auth = base64_encode($this->token);
     $context = stream_context_create([
         "http" => ["header" => "Authorization: Basic $auth"]
     ]);
     $array=[];
     $products=[];

     /*
     Parámetros especiales que tenemos que sacar de la web y que se actualizaban a través del actualizador
     - cantidad_pedido_minimo: lo usamos cuando queremos vender una cantidad de unidades mínimas de un producto. Este valor multiplica en la web el precio
     y concatena al nombre el nº de unidades que se venden.
     - unidad_medida: ese parámetro lo utilizamos para indicar en la web si el producto se venden en metros, kilos, etc... Lo establecemos en función de las
     abreviaturas en los nombres de navision: "v/metro", "v/m", "vta/metro", etc.
     - equivalencia y unidad_medida_equivalencia: son dos campos que se extran de un único campo en Navision para sacar el precio equivalente por unidad de medida (El litro te sale a €€)

     */
     //&filter[date_upd]=>[".$navisionSync->getLastsync()->format("Y-m-d H:i:s")."]
     try{
           //$xml_string=file_get_contents($this->url."/api/products/?display=[reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC", false, $context);
           $xml_string=file_get_contents($this->url."/api/products/?display=[reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description,id_supplier,active]", false, $context);
           $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
           $json = json_encode($xml);
           $array = json_decode($json,TRUE);
           $products=isset($array["products"]["product"])?$array["products"]["product"]:[];
          // $prodPrestashop=isset($array["products"]["product"])?$array["products"]["product"]:[];

        //  dump($prodPrestashop);
       foreach($products as $prodPrestashop){

         //$output->writeln("Prod:".$prodPrestashop);
         $output->writeln("Actualizando producto: ".$prodPrestashop["reference"]." - ".$prodPrestashop["name"]["language"]);
         $cantidad_pedido_minimo=is_array($prodPrestashop["cantidad_pedido_minimo"])?"":$prodPrestashop["cantidad_pedido_minimo"];
         $unidad_medida=is_array($prodPrestashop["unidad_medida"])?"":$prodPrestashop["unidad_medida"];
         $equivalencia=is_array($prodPrestashop["equivalencia"])?"":$prodPrestashop["equivalencia"];
         $unidad_medida_equivalencia=is_array($prodPrestashop["unidad_medida_equivalencia"])?"":$prodPrestashop["unidad_medida_equivalencia"];
         $metatitle=is_array($prodPrestashop["meta_title"]["language"])?"":$prodPrestashop["meta_title"]["language"];
         $metadescription=is_array($prodPrestashop["meta_description"]["language"])?"":$prodPrestashop["meta_description"]["language"];
         $product=$productRepository->findOneBy(["company"=>$company, "code"=>$prodPrestashop["reference"], "deleted"=>0]);

         $proveedorPS=$prodPrestashop["id_supplier"];

         //$output->writeln($cantidad_pedido_minimo." - ".$unidad_medida." - ".$equivalencia." - ".$unidad_medida_equivalencia. " - ".$metatitle. " - ".$metadescription);
         //$output->writeln($cantidad_pedido_minimo." - ".$unidad_medida." - ".$equivalencia." - ".$unidad_medida_equivalencia. " - ".$metatitle. " - ".$metadescription);
         if($prodPrestashop["active"] AND $product){
             $product->setCheckweb(1);
             $doctrine->getManager()->persist($product);
             $webproduct=$WebProductRepository->findOneBy(["product"=>$product->getId()]);
             if($webproduct){
               $webproduct->setMinquantityofsaleweb($cantidad_pedido_minimo);
               $webproduct->setEquivalence($equivalencia);
               $webproduct->setMeasurementunityofequivalence($unidad_medida_equivalencia);
               $webproduct->setMetatitle($metatitle);
               $webproduct->setMetadescription($metadescription);
               if($proveedorPS=="2")  $webproduct->setManomano(1);
               $doctrine->getManager()->persist($webproduct);
               $doctrine->getManager()->flush();
             }
             else{
               $obj=new ERPWebProducts();
               $obj->setProduct($product);
               $company=$companyRepository->find(2);
               $obj->setCompany($company);
               $obj->setMinquantityofsaleweb($cantidad_pedido_minimo);
               $obj->setEquivalence($equivalencia);
               $obj->setMeasurementunityofequivalence($unidad_medida_equivalencia);
               $obj->setMetatitle($metatitle);
               $obj->setMetadescription($metadescription);
               if($proveedorPS=="2")  $obj->setManomano(1);
               $obj->setDateadd(new \Datetime());
               $obj->setDateupd(new \Datetime());
               $obj->setDeleted(0);
               $obj->setActive(1);
               $doctrine->getManager()->persist($obj);
               $doctrine->getManager()->flush();
             }
             $doctrine->getManager()->clear();
         }

       }

     }catch(Exception $e){}

     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"prestashop:webfields"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("prestashop:webfields");
     }

     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($datetime->getTimestamp());
     $doctrine->getManager()->persist($navisionSync);
     $doctrine->getManager()->flush();
     //------   Critical Section END   ------
     //------   Remove Lock Mutex    ------
     fclose($fp);

   }

   public function updateProduct(InputInterface $input, OutputInterface $output){

     $auth = base64_encode($this->token);
     $context = stream_context_create([
         "http" => ["header" => "Authorization: Basic $auth"]
     ]);
     $array=[];
     $products=[];

     try{
           $xml_string=file_get_contents($this->url."/api/products/2284", false, $context);
          // $xml_string=file_get_contents($this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC", false, $context);
           $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
           $xml->product->cantidad_pedido_minimo="3";

           unset($xml->product->manufacturer_name);
           unset($xml->product->quantity);


            $url = "https://www.ferreteriacampollano.com/api/products/2284";
          //  $url= $this->url."/api/products/?display=[id,reference,name,cantidad_pedido_minimo,unidad_medida,equivalencia,unidad_medida_equivalencia,meta_title,meta_description]&filter[reference]=2322290200AC";
            $ch = curl_init();

            $putString = $xml->asXML();
            //dump($putString);
            /** use a max of 256KB of RAM before going to disk */
            $putData = fopen('php://temp/maxmemory:256000', 'w');
            if (!$putData) {
                die('could not open temp memory data');
            }
            fwrite($putData, $putString);
            fseek($putData, 0);

            // Headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml','Authorization: Basic '.base64_encode($this->token)));
            // Binary transfer i.e. --data-BINARY
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            // Using a PUT method i.e. -XPUT
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));

            curl_setopt($ch, CURLOPT_INFILE, $putData);

            $output = curl_exec($ch);
            echo $output;

            // Close the file
            fclose($putData);
            // Stop curl
        //    curl_close($ch);

            if (curl_errno($ch)) {  print curl_error($ch); }
            else {  curl_close($ch); }  // $data contains the result of the post...


      }catch(Exception $e){}


      }
}
?>
