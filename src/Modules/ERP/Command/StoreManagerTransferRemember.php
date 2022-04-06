<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\ERP\Entity\ERPInfoStocks;
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\Globale\Entity\GlobaleCompanies;



class StoreManagerTransferRemember extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:managerRemember')
            ->setDescription('Recordatorio de transferencias de managers')
            ->addArgument('var_store', InputArgument::REQUIRED, '¿Almacen sobre el que informar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $var_store = $input->getArgument('var_store');
        $usersRepository=$doctrine->getRepository(GlobaleUsers::class);
        $locationsRepository=$doctrine->getRepository(ERPStoreLocations::class);
        $productsVariantsRepository=$doctrine->getRepository(ERPProductsVariants::class);
        $stocksRepository=$doctrine->getRepository(ERPStocks::class);
        $stockHistoryRepository=$doctrine->getRepository(ERPStockHistory::class);
        $infoStocksRepository=$doctrine->getRepository(ERPInfoStocks::class);
        $storesRepository=$doctrine->getRepository(ERPStores::class);
        $productsRepository=$doctrine->getRepository(ERPProducts::class);
        $companiesrepository=$doctrine->getRepository(GlobaleCompanies::class);
        $managerepository=$doctrine->getRepository(ERPStoresManagers::class);

        $output->writeln('');
        $output->writeln('ENVIANDO TRASPASOS DE GESTORES');
        $output->writeln('===================================================');
        //$user=$usersRepository->findOneBy(["email"=>$username, "deleted"=>0]);
        //if(!$user) die('- El usuario especificado no existe');
        //$company=$user->getCompany();

        $store=$storesRepository->findOneBy(["code"=>$var_store,"deleted"=>0]);
        if(!$store) {
          die(' - Almacén no encontrado');
        }
        $manager=$managerepository->findOneBy(["name"=>"BABCOCK"]);
        if(!$store) {
          die(' - Gestor no encontrado');
        }
       /*$info_stocks=$infoStocksRepository->findBy(["store"=>$store, "active"=>1, "deleted"=>0]);
        foreach($info_stocks as $infostock){
            $location=$locationsRepository->findOneBy(["name"=>$store->getCode(), "active"=>1, "deleted"=>0]);
            if(!$location) {
              die(' - Ubicación no encontrada');
            }
            $stock=$stocksRepository->findOneBy(["product"=>$infostock->getProduct(), "productvariant"=>$infostock->getProductvariant(), "storelocation"=>$location, "active"=>1, "deleted"=>0]);
            if(!$location) {
                $output->writeln(' - Stock no encontrado');
                continue;
            }
            if($infostock->getMinimumQuantity()>=$stock->getQuantity()){
              //Inform to discotd channel
              if($manager->getDiscordchannel()!=null){
                $channel=$manager->getDiscordchannel();
                $msg="Ref: **".$infostock->getProduct()->getCode()."** - ".$infostock->getProduct()->getName()." realizar traspaso a **".$store->getName()."** - Cantidad: **".($infostock->getMaximunQuantity()-$stock->getQuantity()." unidades.**");
                file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
              }
            }
        }*/


        $info_stocks=$infoStocksRepository->getMinimum($store);
        $output->writeln($info_stocks);
        $output->writeln("Canal:".$manager->getDiscordchannel());
        if($manager->getDiscordchannel()!=null){
          $channel=$manager->getDiscordchannel();
          $msg="**** ------------------ Los siguientes traspasos son al almacén ** ".$store->getName()." ------------------ ****";
          file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
          sleep(1);
        }
        else $output->writeln($channel);

        foreach($info_stocks as $infostock){
          //solo mandamos la información de la talla, no del producto agrupado
          if($infostock["grouped"]=="1"){
              if($infostock["variant_name"]!=NULL){
                $product=$productsRepository->findOneBy(["id"=>$infostock["product_id"]]);
                $info=$infoStocksRepository->findOneBy(["product"=>$infostock["product_id"], "store"=>$store->getId()]);
                if($manager->getDiscordchannel()!=null){
                  $channel=$manager->getDiscordchannel();
                  $msg="Ref: **".$product->getCode()."** - ".$product->getName()." - Talla: ".$infostock["variant_name"]." realizar traspaso a **".$store->getName()."** - Cantidad: **".($info->getMaximunQuantity()-$infostock["quantity"]." unidades.**");
                  file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
                  sleep(1);
                }

              }

          }
          else{
            $product=$productsRepository->findOneBy(["id"=>$infostock["product_id"]]);
            $info=$infoStocksRepository->findOneBy(["product"=>$infostock["product_id"], "store"=>$store->getId()]);
            if($manager->getDiscordchannel()!=null){
              $channel=$manager->getDiscordchannel();
              $msg="Ref: **".$product->getCode()."** - ".$product->getName()." realizar traspaso a **".$store->getName()."** - Cantidad: **".($info->getMaximunQuantity()-$infostock["quantity"]." unidades.**");
              file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
              sleep(1);
            }

          }
      }
  }
}
?>
