<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPInfoStocks;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\Globale\Entity\GlobaleCompanies;
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


}
