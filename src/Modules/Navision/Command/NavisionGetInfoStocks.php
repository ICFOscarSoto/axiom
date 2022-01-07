<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPInfoStocks;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\ERP\Entity\ERPTypesMovements;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetInfoStocks extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getinfostocks')
            ->setDescription('Sync navision principal entities')
            ->addArgument('entity', InputArgument::REQUIRED, '¿Entidad que sincronizar?')
            ->addArgument('manager', InputArgument::OPTIONAL, '¿Gestor que sincronizar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    $entity = $input->getArgument('entity');
    $manager = $input->getArgument('manager');

    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $this->company=$repositoryCompanies->find(2);

    $output->writeln('');
    $output->writeln('Comenzando sincronizacion Navision');
    $output->writeln('==================================');
    switch($entity){
      case 'operations':
        $this->importOperations($input, $output, $manager);
      break;
      case 'all':
        $this->importInfoStocks($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

  public function importInfoStocks(InputInterface $input, OutputInterface $output){
    //------   Create Lock Mutex    ------
    /*$fp = fopen('/tmp/axiom-NavisionGetInfoStocks-importInfoStocks.lock', 'c');
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
      $output->writeln('* Fallo al iniciar la sincronizacion de transferencias: El proceso ya esta en ejecución.');
      exit;
    }*/

    //------   Critical Section START   ------
    $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"infoStocks"]);
    if ($navisionSync==null) {
      $navisionSync=new NavisionSync();
      $navisionSync->setEntity("infoStocks");
      $navisionSync->setLastsync(new \DateTime("@0"));
      $navisionSync->setMaxtimestamp(0);
    }
    $datetime=new \DateTime();

      $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getTransfersShipment.php?from='.$navisionSync->getMaxtimestamp());
      //$json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getTransfersShipment.php');
      $objects=json_decode($json, true);
      $objects=$objects[0];
      $output->writeln('Conseguimos los traspasos');
      foreach ($objects["class"] as $object){
      // buscamos el almacen del traspaso
      $storeRepository=$this->doctrine->getRepository(ERPStores::class);
      $store=$storeRepository->findOneBy(['code'=>$object["destino"]]);
      // buscamos el producto del traspaso
      $productRepository=$this->doctrine->getRepository(ERPProducts::class);
      $product=$productRepository->findOneBy(['code'=>$object["Item No."]]);
      // buscamos la fila de los traspasos del producto y del almacén
      $infostocksRepository=$this->doctrine->getRepository(ERPInfoStocks::class);
      $infostocks=$infostocksRepository->findOneBy(['store'=>$store->getId(), 'product'=>$product->getId()]);
      if ($infostocks==null) {
        $infostocks= new ERPInfoStocks();
        $infostocks->setDateadd(new \DateTime);
        $infostocks->setDateupd(new \DateTime);
        $infostocks->setProduct($product);
        $infostocks->setStore($store);
        $infostocks->setActive(1);
        $infostocks->setDeleted(0);
      }
      // actualizamos el stock del pendiente de recibir
      $received=(int)$object["Quantity"];
      $infostocks->setPendingToReceive($infostocks->getPendingToReceive()-$received);
      $this->doctrine->getManager()->persist($infostocks);
      $this->doctrine->getManager()->flush();
      $this->doctrine->getManager()->clear();
     }
    //fclose($fp);
  }



  public function importOperations(InputInterface $input, OutputInterface $output, $manager=null){
    $managerRepository=$this->doctrine->getRepository(ERPStoresManagers::class);
    $productRepository=$this->doctrine->getRepository(ERPProducts::class);
    $managerId=$managerRepository->findOneBy(["name"=>$manager]);
    $products=$productRepository->getProductsByManager($managerId->getId());
    foreach ($products as $product){
      $operationsLinesRepository=$this->doctrine->getRepository(ERPStoresManagersOperationsLines::class);
      $operationsLines=$operationsLinesRepository->findBy(['product'=>$product["product_id"]]);
      $item=$productRepository->findOneById($product["product_id"]);
      foreach ($operationsLines as $line) {
        $operationsRepository=$this->doctrine->getRepository(ERPStoresManagersOperations::class);
        $operation=$operationsRepository->findOneById($line->getOperation()->getId());
        $storeLocationsRepository=$this->doctrine->getRepository(ERPStoreLocations::class);
        $storeLocation=$storeLocationsRepository->findOneBy(["name"=>$operation->getStore()->getCode()]);
        $usersRepository=$this->doctrine->getRepository(GlobaleUsers::class);
        $user=$usersRepository->findOneById([$operation->getAgent()->getId()]);
        $quantity=-(int)$line->getQuantity();
        $typesRepository=$this->doctrine->getRepository(ERPTypesMovements::class);
        $type=$typesRepository->findOneBy(["name"=>"Salida gestor"]);
        $stockHistory=new ERPStockHistory();
        $stockHistory->setProduct($item);
        $stockHistory->setLocation($storeLocation);
        $stockHistory->setStore($operation->getStore());
        $stockHistory->setUser($user);
        $stockHistory->setDateadd($operation->getDate());
        $stockHistory->setDateupd(new \Datetime());
        $stockHistory->setNumOperation($operation->getId());
        $stockHistory->setQuantity($quantity);
        $stockHistory->setType($type);
        $stockHistory->setActive(true);
        $stockHistory->setDeleted(false);
        $this->doctrine->getManager()->merge($stockHistory);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();

      }
    }
  }


}
