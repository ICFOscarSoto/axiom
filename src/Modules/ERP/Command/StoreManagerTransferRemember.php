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
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPStocks;
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
        $variantsRepository=$doctrine->getRepository(ERPVariants::class);
        $stocksRepository=$doctrine->getRepository(ERPStocks::class);
        $storesRepository=$doctrine->getRepository(ERPStores::class);
        $storeLocationsRepository=$doctrine->getRepository(ERPStoreLocations::class);
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

        $stocks=$stocksRepository->getMinimum($store);
        if($manager->getDiscordchannel()!=null){
          $channel=$manager->getDiscordchannel();
          $msg="**** ------------------ Los siguientes traspasos son al almacén ** ".$store->getName()." ------------------ ****";
          file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
          sleep(1);
        }

        foreach($stocks as $stock){
          //solo mandamos la información de la talla, no del producto agrupado
          if($stock["grouped"]=="1"){
              if($stock["variant_name"]!=NULL){
                $product=$productsRepository->findOneBy(["id"=>$stock["product_id"]]);
                $variant=$variantsRepository->findOneBy(["name"=>$stock["variant_name"]]);
                $productvariant=$productsVariantsRepository->findOneBy(["product"=>$product,"variant"=>$variant]);
                $storelocation=$storeLocationsRepository->findOneBy(["store"=>$store]);
                  $istock=$stocksRepository->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation]);
                  $minstock=$istock->getMinimumQuantity();
                  if($stock["quantity"]<$minstock){
                    if($manager->getDiscordchannel()!=null){
                      $channel=$manager->getDiscordchannel();
                      $msg="Ref: **".$product->getCode()."** - ".$product->getName()." - Talla: ".$stock["variant_name"]." realizar traspaso a **".$store->getName()."** - Cantidad: **".($istock->getMaxstock()-$stock["quantity"]." unidades.**");
                      file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
                      sleep(1);
                    }
                  }
              }

          }
          else{
            $product=$productsRepository->findOneBy(["id"=>$stock["product_id"]]);
            $productvariant=$productsVariantsRepository->findOneBy(["product"=>$product,"variant"=>null]);
            $storelocation=$storeLocationsRepository->findOneBy(["store"=>$store]);
              $istock=$stocksRepository->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation]);
              if($manager->getDiscordchannel()!=null){
                $channel=$manager->getDiscordchannel();
                $msg="Ref: **".$product->getCode()."** - ".$product->getName()." realizar traspaso a **".$store->getName()."** - Cantidad: **".($istock->getMaxstock()-$stock["quantity"]." unidades.**");
                file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
                sleep(1);
              }

          }
      }
  }
}
?>
