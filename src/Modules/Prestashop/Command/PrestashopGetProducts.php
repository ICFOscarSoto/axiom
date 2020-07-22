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
       $output->writeln('* Fallo al iniciar la sincronizacion de products con prestashop: El proceso ya esta en ejecución.');
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


}
?>
