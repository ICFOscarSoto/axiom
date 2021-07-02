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
use App\Modules\Globale\Entity\GlobaleCompanies;



class ImportStocks extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:importStocks')
            ->setDescription('Importación de inventario')
            ->addArgument('user', InputArgument::REQUIRED, '¿Nombre de usuario que lanza el proceso?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $map[]=["sku"=>false,"variantname"=>false,"variantvalue"=>false,"minstock"=>false,"maxstock"=>false,"qty"=>false,"store"=>false,"location"=>false];
        $username = $input->getArgument('user');
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $usersRepository=$doctrine->getRepository(GlobaleUsers::class);
        $locationsRepository=$doctrine->getRepository(ERPStoreLocations::class);
        $productsVariantsRepository=$doctrine->getRepository(ERPProductsVariants::class);
        $stocksRepository=$doctrine->getRepository(ERPStocks::class);
        $stockHistoryRepository=$doctrine->getRepository(ERPStockHistory::class);
        $infoStocksRepository=$doctrine->getRepository(ERPInfoStocks::class);
        $storesRepository=$doctrine->getRepository(ERPStores::class);
        $productsRepository=$doctrine->getRepository(ERPProducts::class);
        $companiesrepository=$doctrine->getRepository(GlobaleCompanies::class);

        $output->writeln('');
        $output->writeln('IMPORTACION DE STOCKS DESDE CSV');
        $output->writeln('===================================================');
        $user=$usersRepository->findOneBy(["email"=>$username, "deleted"=>0]);
        if(!$user) die('- El usuario especificado no existe');
        $company=$user->getCompany();
        $output->writeln('- Abriendo fichero');
        $file='/home/david/Documentos/import_stocks.csv';
        if(!file_exists($file)) die('- No existe el fichero CSV de importacion');
        $handle = fopen($file,'r');
        if($handle===false) die('- No se pudo abrir el fichero CSV de importacion');
        $output->writeln('- Comprobando estructura de fichero');
        $headers=fgetcsv($handle);
        if($headers===null) die('- Error al leer los encabezados del fichero CSV');
        if(!is_array($headers)) die('- Error al leer los encabezados del fichero CSV');
        //array_map(array($this, "find_header"), $headers);
        $map["sku"]=array_search('sku',$headers);
        $map["variantname"]=array_search('variantname',$headers);
        $map["variantvalue"]=array_search('variantvalue',$headers);
        $map["qty"]=array_search('qty',$headers);
        $map["minstock"]=array_search('minstock',$headers);
        $map["maxstock"]=array_search('maxstock',$headers);
        $map["store"]=array_search('store',$headers);
        $map["location"]=array_search('location',$headers);

        if($map["sku"]===false) die('- Falta la columna obligatoria sku');
        if($map["variantname"]===false) die('- Falta la columna obligatoria variantname');
        if($map["variantvalue"]===false) die('- Falta la columna obligatoria variantvalue');
        if($map["qty"]===false) die('- Falta la columna obligatoria qty');
        if($map["store"]===false) die('- Falta la columna obligatoria store');
        if($map["location"]===false) die('- Falta la columna obligatoria location');

        $output->writeln('- Procesando datos del fichero');
        while ( ($data = fgetcsv($handle) ) !== FALSE ) {
          $product=$productsRepository->findOneBy(["code"=>$data[$map["sku"]], "deleted"=>0]);
          if(!$product) {
            $output->writeln('   -> '.$data[$map["sku"]].' - Producto no encontrado');
            continue;
          }
          $variant=null;
          if((isset($data[$map["variantname"]]) && $data[$map["variantname"]]!=null) || (isset($data[$map["variantvalue"]]) && $data[$map["variantvalue"]]!=null)){
            //TODO: SELECT VARIANT
          }

          $store=$storesRepository->findOneBy(["code"=>$data[$map["store"]],"deleted"=>0]);
          if(!$store) {
            $output->writeln('   -> '.$data[$map["sku"]].' - Almacén no encontrado');
            continue;
          }

          $location=$locationsRepository->findOneBy(["name"=>$data[$map["location"]], "store"=>$store, "deleted"=>0]);
          if(!$location) {
            $output->writeln('   -> '.$data[$map["sku"]].' - Ubicación no encontrada');
            continue;
          }
          $output->writeln('   -> '.$data[$map["sku"]]);

          $stock=$stocksRepository->findOneBy(["product"=>$product, "productvariant"=>$variant, "storelocation"=>$location, "deleted"=>0]);
          if(!$stock){
            $stock=new ERPStocks();
            $stock->setProduct($product);
            $stock->setProductvariant($variant);
            $stock->setCompany($company);
            $stock->setStorelocation($location);
            $stock->setAuthor($user);
            $stock->setDateadd(new \Datetime());
            $stock->setActive(true);
            $stock->setDeleted(false);
          }else{
            if($stock->getQuantity()!=($data[$map["qty"]]*1)){
              //Save Stock History
              $stockHistory=new ERPStockHistory();
              $stockHistory->setProduct($product);
              $stockHistory->setProductvariant($variant);
              $stockHistory->setLocation($location);
              $stockHistory->setStore($store);
              $stockHistory->setUser($user);
              $stockHistory->setPreviousqty($stock->getQuantity());
              $stockHistory->setNewqty($data[$map["qty"]]*1);
              $stockHistory->setDateadd(new \Datetime());
              $stockHistory->setDateupd(new \Datetime());
              $stockHistory->setActive(true);
              $stockHistory->setDeleted(false);
              $doctrine->getManager()->persist($stockHistory);
              $doctrine->getManager()->flush();
            }
          }
          $stock->setQuantity($data[$map["qty"]]);
          $stock->setLastinventorydate(new \Datetime());
          $stock->setDateupd(new \Datetime());
          $doctrine->getManager()->persist($stock);
          $doctrine->getManager()->flush();

          //INFO Stock
          if(!$map["minstock"] || !$map["maxstock"]) continue;
          if(!((isset($data[$map["minstock"]]) && $data[$map["minstock"]]!=null) || (isset($data[$map["maxstock"]]) && $data[$map["maxstock"]]!=null))) continue;
          $infoStocks=$infoStocksRepository->findOneBy(["product"=>$product,"productvariant"=>$variant,"store"=>$store, "deleted"=>0]);
          if(!$infoStocks){
            $infoStocks=new ERPInfoStocks();
            $infoStocks->setProduct($product);
            $infoStocks->setProductvariant($variant);
            $infoStocks->setStore($store);
            $infoStocks->setPendingToReceive(0);
            $infoStocks->setPendingToServe(0);
            $infoStocks->setAuthor($user);
            $infoStocks->setDateadd(new \Datetime());
            $infoStocks->setActive(true);
            $infoStocks->setDeleted(false);
          }
          $infoStocks->setMinimumQuantity($data[$map["minstock"]]?$data[$map["minstock"]]:0);
          $infoStocks->setMaximunQuantity($data[$map["maxstock"]]?$data[$map["maxstock"]]:0);
          $infoStocks->setDateupd(new \Datetime());
          $doctrine->getManager()->persist($infoStocks);
          $doctrine->getManager()->flush();
        }


  }
}
?>
