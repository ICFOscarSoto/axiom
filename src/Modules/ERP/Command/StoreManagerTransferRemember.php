<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\ERP\Entity\ERPStoresManagers;

class StoreManagerTransferRemember extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:managerRemember')
            ->setDescription('Recordatorio de transferencias de managers')
            ->addArgument('var_store', InputArgument::REQUIRED, '¿Gestor sobre el que informar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output){
    $doctrine = $this->getContainer()->get('doctrine');
    $var_store = $input->getArgument('var_store');
    $stocksRepository=$doctrine->getRepository(ERPStocks::class);
    $storesRepository=$doctrine->getRepository(ERPStores::class);
    $managerepository=$doctrine->getRepository(ERPStoresManagers::class);

    $output->writeln('');
    $output->writeln('ENVIANDO TRASPASOS DE GESTORES');
    $output->writeln('===================================================');
    /*$store=$storesRepository->findOneBy(["code"=>$var_store,"deleted"=>0]);
    if(!$store) {
      die(' - Almacén no encontrado');
    }*/
    $manager=$managerepository->findOneBy(["name"=>$var_store]);
    if(!$manager && $manager->getDiscordchannel()==null) {
      die(' - Gestor no encontrado');
    }
    $stores=$storesRepository->findBy(["managed_by"=>$manager, "active"=>1, "deleted"=>0]);
    foreach ($stores as $store){
      $channel=$manager->getDiscordchannel();
      $msg="**** ------------------ Los siguientes traspasos son al almacén ** ".$store->getName()." ------------------ ****";
      file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
      sleep(1);
      $typeofnotifications=$store->getTypeofnotifications();
      switch ($typeofnotifications){
        case 0:
          break;
        case 1:
          $stocks=$stocksRepository->getMinimum($store);
          $this->sendNotifications($stocks,$store,$manager);
          break;
        case 2:
          if (empty($stocksRepository->getMinimum($store))){
            $stocks=$stocksRepository->getAll($store);
            $this->sendNotifications($stocks,$store,$manager);
          }
          break;
      }
    }
  }

  public function sendNotifications($stocks,$store,$manager) {    
    $doctrine = $this->getContainer()->get('doctrine');
    $productsRepository=$doctrine->getRepository(ERPProducts::class);
    $productsVariantsRepository=$doctrine->getRepository(ERPProductsVariants::class);
    $variantsRepository=$doctrine->getRepository(ERPVariants::class);    
    $storeLocationsRepository=$doctrine->getRepository(ERPStoreLocations::class);
    $vendingMachinesRepository=$doctrine->getRepository(ERPStoresManagersVendingMachines::class);
    $stocksRepository=$doctrine->getRepository(ERPStocks::class);

    $channelsRepository=$doctrine->getRepository(ERPStoresManagersVendingMachinesChannels::class);
    foreach($stocks as $stock){
      //solo mandamos la información de la talla, no del producto agrupado
      if($stock["grouped"]=="1"){
        if($stock["variant_name"]!=NULL){
          $product=$productsRepository->findOneBy(["id"=>$stock["product_id"],"active"=>1, "deleted"=>0]);
          $variant=$variantsRepository->findOneBy(["name"=>$stock["variant_name"],"active"=>1, "deleted"=>0]);
          $productvariant=$productsVariantsRepository->findOneBy(["product"=>$product,"variant"=>$variant, "active"=>1, "deleted"=>0]);
          $storelocation=$storeLocationsRepository->findOneBy(["store"=>$store, "active"=>1, "deleted"=>0]);
          $istock=$stocksRepository->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation, "active"=>1, "deleted"=>0]);
          if($manager->getDiscordchannel()!=null){
            $channel=$manager->getDiscordchannel();
            $msg="Ref: **".$product->getCode()."** - ".$product->getName()." - Talla: ".$stock["variant_name"]." realizar traspaso a **".$store->getName()."** - Cantidad: **".$istock->getMaxstock()-$stock["quantity"]." unidades.**";
            file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
            sleep(1);
          }
        }
      }
      else{
        $product=$productsRepository->findOneBy(["id"=>$stock["product_id"], "active"=>1, "deleted"=>0]);
        if ($product==null) continue;
        $productvariant=$productsVariantsRepository->findOneBy(["product"=>$product,"variant"=>null, "active"=>1, "deleted"=>0]);
        $storelocation=$storeLocationsRepository->findOneBy(["store"=>$store,"active"=>1, "deleted"=>0]);
        $istock=$stocksRepository->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation,"active"=>1, "deleted"=>0]);
        $vendingMachine=$vendingMachinesRepository->findOneBy(["storelocation"=>$storelocation, "active"=>1, "deleted"=>0]);
        $channelVM=$channelsRepository->findOneBy(["vendingmachine"=>$vendingMachine, "product"=>$product, "active"=>1, "deleted"=>0]);
        if($manager->getDiscordchannel()!=null){
          $channelDiscord=$manager->getDiscordchannel();
          if ($channelVM!=null && $channelVM->getMultiplier()>1){
            $msg="Ref: **".$product->getCode()."** - ".$product->getName()." realizar traspaso a **".$store->getName()."** - Cantidad: **".($istock->getMaxstock()-$stock["quantity"])/$channelVM->getMultiplier()." paquetes de ".$channelVM->getMultiplier()." unidades cada paquete.**";
          }
          else {
            $msg="Ref: **".$product->getCode()."** - ".$product->getName()." realizar traspaso a **".$store->getName()."** - Cantidad: **".($istock->getMaxstock()-$stock["quantity"]." unidades.**");
          }
          file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channelDiscord.'&msg='.urlencode($msg));
          sleep(1);
        }
      }
    }
  }

}
?>
