<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPPurchasesOrders;
use App\Modules\ERP\Entity\ERPPurchasesOrdersLine;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class AXIOMGetOrders extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getmanufacturers')
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
      case 'purchasesOrders': $this->createOrders($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }


  public function createOrders(InputInterface $input, OutputInterface $output){
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
    $repositoryPurchasesOrders=$this->doctrine->getRepository(ERPPurchasesOrders::class);
    $repositoryPurchasesOrdersLines=$this->doctrine->getRepository(ERPPurchasesOrdersLines::class);
    $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
    $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
    $repositoryVariants=$this->doctrine->getRepository(ERPProductsVariants::class);

    $orders=$repositoryPurchasesOrders->findAll();
    foreach($orders as $order){
      if ($order->getAuthor()->getName()=="Administrador") $author=null;
      else $author=$order->getAuthor()->getEmail();

      $orderHeader[]=["No."=>$order->getCode(),
      "Buy-from Vendor No."=>$order->getSupplier()->getCode(),
      "Assigned User ID"=>$author,
      "Purchaser Code"=>$order->getAgent()->getEmail(),
      "Buy-from Vendor Name"=>substr($order->getSuppliername(),0,50),
      "Buy-from Vendor Name 2"=>substr($order->getSuppliername(),50,50),
      "Buy-from Address"=>substr($order->getSupplieraddress(),0,50),
      "Buy-from Address 2"=>substr($order->getSupplieraddress(),50,50),
      "Buy-from Post Code"=>$order->getSupplierpostcode(),
      "Buy-from City"=>$order->getSuppliercity(),
      "Buy-from County"=>$order->getSupplierstate(),
      "No oferta relacionada"=>$order->getPurchasesbudget()->getCode(),
      "Ship-to Post Code"=>$order->getSupplierpostcode(),
      "Status"=>$order->getStatus(),
      "Payment Method Code"=>$order->getSupplier()->getPaymentmethod()->getPaymentcode(),
      "VAT Registration No."=>$order->getVat(),
      "Document Date" => $order->getDate(),
      "Fecha 1.lanzamiento" => $order->getDateofferend(),
      //"Line Amount"=>$order->getTotalnet(),
      //"Line Discount Amount"=>$order->getTotaldto(),
      //"Amount Including VAT"=>$order->getTotal()
      ];



      $output->writeln(json_encode($orderHeader));
      //$result=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createEAN132.php?json='.json_encode($orderHeader));
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



}
?>
