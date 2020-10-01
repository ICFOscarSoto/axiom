<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPSalesBudgets;
use App\Modules\ERP\Entity\ERPSalesBudgetsLines;
use App\Modules\ERP\Entity\ERPSalesOrders;
use App\Modules\ERP\Entity\ERPSalesOrdersLines;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetSalesOrders extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getsalesorders')
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
      case 'salesorders': $this->importSaleOrders($input, $output);
      break;
      case 'all':
        $this->importSaleOrders($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

    public function importSaleOrders(InputInterface $input, OutputInterface $output){
      //------Sync Sales budgets    ------
      $command = $this->getApplication()->find('navision:getsalesbudgets');
      $arguments = [
          'entity'    => 'salesbudgets'
      ];
      $cmdProductsInput = new ArrayInput($arguments);
      $cmdProductsreturn = $command->run($cmdProductsInput, $output);



      //------   Create Lock Mutex    ------
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetSalesOrders-importSalesOrders.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionGetSalesOrders-importSalesOrders.lock', 'c');
      }
      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la sincronizacion de presupuestos: El proceso ya esta en ejecución.');
        exit;
      }

      //------   Critical Section START   ------
      $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"salesOrders"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setMaxtimestamp(0);
      }
      $datetime=new \DateTime();
      $output->writeln('* Sincronizando pedidos de venta....');
      $ctx = stream_context_create(array('http'=>
                    array('timeout' => 1800)
                  ));
      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getSalesOrders.php?from='.$navisionSync->getMaxtimestamp(), false, $ctx);
      $objects=json_decode($json, true);
      $objects=$objects[0];
      $repositoryPaymentMethods=$this->doctrine->getRepository(ERPPaymentMethods::class);
      $repositorySalesBudgets=$this->doctrine->getRepository(ERPSalesBudgets::class);
      $repositorySalesBudgetsLines=$this->doctrine->getRepository(ERPSalesBudgetsLines::class);
      $repositorySalesOrders=$this->doctrine->getRepository(ERPSalesOrders::class);
      $repositorySalesOrdersLines=$this->doctrine->getRepository(ERPSalesOrdersLines::class);
      $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
      $repositoryUsers=$this->doctrine->getRepository(GlobaleUsers::class);
      $repositoryCustomers=$this->doctrine->getRepository(ERPCustomers::class);
      $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
      $repositoryCurrencies=$this->doctrine->getRepository(GlobaleCurrencies::class);

      //Disable SQL logger
      $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

      foreach ($objects["class"] as $key=>$object){
        $company=$repositoryCompanies->find(2);
        $currency=$repositoryCurrencies->findOneBy(["name"=>"Euro"]);
        $salesBudget=$repositorySalesBudgets->findOneBy(["code"=>$object["salesbudget"], "deleted"=>0]);
        $output->writeln('  - '.$object["code"].' - '.$object["customer"]);
        $obj=$repositorySalesOrders->findOneBy(["code"=>$object["code"]]);
        $cost=0;
        $oldobj=$obj;
        if ($obj==null) {
          $obj=new ERPSalesOrders();
          $obj->setCode($object["code"]);
          $number=intval(substr($object["code"], 5));
          $obj->setNumber($number);
          $obj->setCompany($company);
          $obj->setCurrency($currency);
          $obj->setDateadd(new \Datetime());
          $obj->setDateupd(new \Datetime());
          $obj->setDeleted(0);
          $obj->setActive(1);
        }
         $customer=$repositoryCustomers->findOneBy(["code"=>$object["customer"]]);
         if($customer==NULL) {
           //$output->writeln('     ! Saltado no existe el cliente');
           //continue;
         }
         $obj->setCustomer($customer);

         $paymentmethod=$repositoryPaymentMethods->findOneBy(["paymentcode"=>$object["paymentcode"], "deleted"=>0]);
         $obj->setPaymentmethod($paymentmethod);

         $salesBudget=$repositorySalesBudgets->findOneBy(["code"=>$object["salesbudget"], "deleted"=>0]);
         $obj->setSalesbudget($salesBudget);

         $author=$repositoryUsers->findOneBy(["email"=>$object["author"]]);
         if($author==NULL) $author=$repositoryUsers->findOneBy(["name"=>"Administrador"]);

         $agent=$repositoryUsers->findOneBy(["email"=>$object["agent"]]);
         if($agent==NULL) $agent=$author;
         $obj->setAuthor($author);
         $obj->setAgent($agent);
         $obj->setVat($object["vat"]);
         $obj->setCustomername($object["customername"]);
         $obj->setCustomeraddress($object["customeraddress"]);
         $obj->setCustomercountry($customer?$customer->getCountry():null);
         $obj->setCustomercity($object["customercity"]);
         $obj->setCustomerstate($customer?$customer->getState()!=null?$customer->getState()->getName():null:null);
         $obj->setCustomerpostcode($object["customerpostcode"]);

         $obj->setShiptoname($object["shiptoname"]);
         $obj->setShiptoaddress($object["shiptoaddress"]);
         $obj->setShiptocountry($customer?$customer->getCountry():null);
         $obj->setShiptocity($object["shiptocity"]);
         $obj->setShiptostate($customer?$customer->getState()!=null?$customer->getState()->getName():null:null);
         $obj->setShiptopostcode($object["shiptopostcode"]);

         $obj->setCustomercode($object["customer"]);
         $obj->setDate(date_create_from_format("Y-m-d H:i:s.u",$object["date"]["date"]));
         $obj->setDateofferend(date_create_from_format("Y-m-d H:i:s.u",$object["enddate"]["date"]));

         $obj->setIrpf(0);
         $obj->setIrpfperc(0);
         $obj->setSurcharge(0);
         $obj->setTaxexempt(0);
         $obj->setCost(round($object["cost"],2));
         $obj->setTotalnet($object["linestotal"]);
         $obj->setTotaldto($object["dto"]);
         $obj->setTotalbase($object["base"]);
         $obj->setTotaltax($object["vattotal"]);
         $obj->setTotalsurcharge(0);
         $obj->setTotalirpf(0);
         $obj->setTotal($object["total"]);
         $obj->setDateupd(new \Datetime());

         $this->doctrine->getManager()->persist($obj);
         $this->doctrine->getManager()->flush();

        //Process lines
        $totalNet=0;
        $totalDto=0;
        $totalBase=0;
        $totalTax=0;
        $totalSurcharge=0;
        $totalIrpf=0;
        $total=0;
        foreach($object["lines"] as $key=>$line){
          $output->writeln('      -> Linea '.$line["linenum"].' - '.$line["reference"]);
          $objLine=$repositorySalesOrdersLines->findOneBy(["salesorder"=>$obj,"linenum"=>$line["linenum"]]);
          if ($objLine==null) {
            $objLine=new ERPSalesOrdersLines();
            $objLine->setSalesorder($obj);
            $objLine->setLinenum($line["linenum"]);
            $objLine->setDateadd(new \Datetime());
            $objLine->setDateupd(new \Datetime());
            $objLine->setDeleted(0);
            $objLine->setActive(1);
          }
          $product=$repositoryProducts->findOneBy(["code"=>$line["reference"]]);
          if($product==NULL){
             $output->writeln('     ! Saltado no existe el producto');
             continue;
          }
          $objLine->setCode($line["reference"]);
          $objLine->setName($line["description"]);
          $objLine->setProduct($product);
          $objLine->setUnitprice($line["price"]);
          $objLine->setQuantity($line["quantity"]);
          $objLine->setCost(round($line["cost"],2));
          $objLine->setTaxperc($line["taxperc"]);
          $objLine->setTaxunit(round($line["linetotal"]*$line["taxperc"]/100,2));
          $objLine->setDtoperc($line["discountperc"]);
          $objLine->setDtounit($line["discounttotal"]);
          $objLine->setSurchargeperc($line["surchargeperc"]);
          $objLine->setSurchargeunit(round($line["linetotal"]*$line["surchargeperc"]/100,2));
          $objLine->setSubtotal($line["price"]*$line["quantity"]);
          $objLine->setTotal($line["total"]);
          $objLine->setDateupd(new \Datetime());
          $this->doctrine->getManager()->persist($objLine);
          $this->doctrine->getManager()->flush();
          //$output->writeln('     - Grabado ID: '.$objLine->getId());

        }
        //$totalBase=$totalNet-$totalDto;
        //$this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();

      }
      $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"salesOrders"]);
      if ($navisionSync==null) {
        $navisionSync=new NavisionSync();
        $navisionSync->setEntity("salesOrders");
      }
      $navisionSync->setLastsync($datetime);
      $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
      $this->doctrine->getManager()->persist($navisionSync);
      $this->doctrine->getManager()->flush();
      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
    }

}
?>
