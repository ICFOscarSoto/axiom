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
use App\Modules\Navision\Entity\NavisionTransfers;
use App\Modules\Globale\Entity\GlobaleCompanies;



class StoreManagerWaitingReceive extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:waitingReceive')
            ->setDescription('Recordatorio de transpasos pendientes de recibir')
            ->addArgument('var_manager', InputArgument::REQUIRED, 'Gestor sobre el que informar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $var_manager = $input->getArgument('var_manager');
        $storesRepository=$doctrine->getRepository(ERPStores::class);
        $transfersRepository=$doctrine->getRepository(NavisionTransfers::class);
        $managerepository=$doctrine->getRepository(ERPStoresManagers::class);

        $output->writeln('');
        $output->writeln('Enviando avisos de traspasos pendientes de recibir');
        $output->writeln('===================================================');

        $unreceivedTransfers=$storesRepository->getUnreceivedTransfers($var_manager);
        $manager=$managerepository->findOneBy(["name"=>$var_manager]);
        foreach ($unreceivedTransfers as $name){
          $transfer=$transfersRepository->findOneBy(["name"=>$name["name"]]);
          $msg="El traspaso ".$transfer->getName()." esta pendiente de recibir en el almacen ".$transfer->getDestinationstore()->getName()." desde que se envío el día ".date_format($transfer->getDateadd(), "d/m/Y");
          if ($manager->getIncidentchannel()!=null) file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$manager->getIncidentchannel().'&msg='.urlencode($msg));
          sleep(1);
        }

  }
}
?>
