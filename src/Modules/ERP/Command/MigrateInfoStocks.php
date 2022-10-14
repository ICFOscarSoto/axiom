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
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\Globale\Entity\GlobaleCompanies;



class MigrateInfoStocks extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:migrateinfostocks')
            ->setDescription('Migración infostocks')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $doctrine = $this->getContainer()->get('doctrine');

        $output->writeln('');
        $output->writeln('MIGRACIÓN DE ERPINFOSTOCKS --> ERPSTOCKS');
        $output->writeln('========================================');
        $em = $doctrine->getManager();

        $query ="SELECT
                concat(sl.id,'-',IFNULL(pv.id,0)) as label,
                ifnull(inf.minimum_quantity,0) as minstock,
                ifnull(inf.maximun_quantity,0) as maxstock
                FROM erpinfo_stocks inf
                LEFT JOIN erpstore_locations sl ON inf.store_id=sl.store_id
                LEFT JOIN erpproducts_variants pv ON if(inf.productvariant_id IS NULL,
                pv.product_id=inf.product_id AND pv.variant_id IS NULL, pv.id=inf.productvariant_id);";

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();

        $infostocks = $statement->fetchAll();

        $i=0;
        foreach($infostocks as $key=>$value){
          $query ="SELECT
                  id,
                  ifnull(minstock,0) as minstock,
                  ifnull(maxstock,0) as maxstock
                  FROM erpstocks where concat(storelocation_id,'-',IFNULL(productvariant_id,0))='".$value['label']."';";
          $statement = $em->getConnection()->prepare($query);
          $statement->execute();

          $stock = $statement->fetchAll();
          if($stock && count($stock)>0){
            $id = $stock[0]['id'];
            $minstock = 'null';
            $maxstock = 'null';       
            if ($value['minstock'])
              $minstock = $value['minstock'];
            if ($value['maxstock'])
              $maxstock = $value['maxstock'];
            /*$minstock = intval($value['minstock']);
            $maxstock = intval($value['maxstock']);
            if (intval($stock[0]['minstock'])>$minstock)
              $minstock = intval($stock[0]['minstock']);
            if (intval($stock[0]['maxstock'])<$maxstock)
                $maxstock = intval($stock[0]['maxstock']);*/
            $query ="update erpstocks set minstock=".$minstock.", maxstock=".$maxstock." where id=".$id.";";
            $statement = $em->getConnection()->prepare($query);
            $rows =$statement->execute();
            $i++;
            $output->writeln($i.' - Id. Stock:'.$id.' - MinStock:'.$minstock.' - Maxstock:'.$maxstock.' - Líneas afectadas:'.$rows);
          }
        }

  }
}
?>
