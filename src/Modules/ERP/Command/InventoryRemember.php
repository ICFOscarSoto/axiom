<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreTickets;
use App\Modules\Globale\Entity\GlobaleCompanies;

class InventoryRemember extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:inventoryRemember')
            ->setDescription('Recordatorio de inventario')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $usersRepository=$doctrine->getRepository(GlobaleUsers::class);
        $departmentsRepository=$doctrine->getRepository(HRDepartments::class);
        $workersRepository=$doctrine->getRepository(HRWorkers::class);
        $clocksRepository=$doctrine->getRepository(HRClocks::class);
        $storesRepository=$doctrine->getRepository(ERPStores::class);
        $storeTicketsRepository=$doctrine->getRepository(ERPStoreTickets::class);
        $companiesrepository=$doctrine->getRepository(GlobaleCompanies::class);
        $output->writeln('');
        $output->writeln('Obteniendo Incidencias con productos para inventariar');
        $output->writeln('===================================================');
        $date= new \DateTime();

      //  $date= new \DateTime();

        $stores=$storesRepository->getInventoryStores();
        dump($stores);
        foreach($stores as $store)
        {

            $tickets=$storeTicketsRepository->getTicketsforInventory($store["id"]);
            dump($tickets);
            $inventory=null;
            if(!empty($tickets))
            {
              foreach($tickets as $ticket){
                $storeticket=$storeTicketsRepository->findOneBy(["id"=>$ticket["id"], "active"=>1, "deleted"=>0]);
                $product=$storeticket->getProduct();
                $inventory[]= $product->getCode()." - ".$product->getName().">> https://axiom.ferreteriacampollano.com/es/ERP/storetickets/solved/".$storeticket->getId();
              }
            }


            if(!empty($inventory)){

            //Send notification to inventory manager.
            $agent=$usersRepository->findOneBy(["id"=>$store["inventorymanager_id"],"active"=>1,"deleted"=>0]);
            $channel_agent=$agent->getDiscordchannel();

            //Check if the user has a worker associated
            $worker=$workersRepository->findOneBy(["user"=>$agent, "active"=>1, "deleted"=>0]);
            if($worker){
                //Check if worker is working now
                if(!$workersRepository->isWorking($worker)){
                  $output->writeln('   - El trabajador no esta trabajando ahora');
                }

                else{

                  $msg_title=":bell: INVENTARIO ".$store["name"]." (".$date->format("d/m/Y").") :bell:\n¡Hola ".$agent->getName()."! Te paso el listado diario de referencias que hay que inventariar en ".$store["name"]." :";
                  $msg_products="";
                  foreach ($inventory as $reference)
                  {
                      $msg_products=$msg_products."**".$reference."**\n";
                  }
                  file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel_agent.'&msg='.urlencode($msg_title."\n\n".$msg_products));
                  $output->writeln('   - Notificación recordatorio enviada al gestor de '.$store["name"]);
                }
            }

          }

      }

  }
}
?>
