<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\Globale\Entity\GlobaleCompanies;



class StoresManagersVendingMachinesTransfersRemember extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:storesManagersVendingMachinesTransfers')
            ->setDescription('Recordatorio de transferencias de maquinas expendedoras')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $usersRepository=$doctrine->getRepository(GlobaleUsers::class);
        $vendingMachinesRepository=$doctrine->getRepository(ERPStoresManagersVendingMachines::class);
        $vendingMachinesChannelsRepository=$doctrine->getRepository(ERPStoresManagersVendingMachinesChannels::class);

        $output->writeln('');
        $output->writeln('TRASPASOS A MAQUINAS EXPENDEDORAS');
        $output->writeln('===================================================');

        $machines = $vendingMachinesRepository->findBy(["active"=>1, "deleted"=>0]);

        foreach($machines as $machine){
          $announced=false;
          if($machine->getManager() && $machine->getManager()->getDiscordchannel()!='' && $machine->getManager()->getActive() && !$machine->getManager()->getDeleted()){
              $output->writeln("  - Comprobando máquina ".$machine->getName());
              $channels=$vendingMachinesChannelsRepository->findBy(["vendingmachine"=> $machine,"active"=>1, "deleted"=>0]);
              foreach($channels as $channel){
                if(($channel->getQuantity()<$channel->getMinquantity()) && $channel->getProductcode()!=null){
                  //Comunicamos la cantidad que hay que reaprovisionar en esta maquina
                  if(!$announced){
                      //Si no hemos mostrado otro reaprovisionamiento de esta maquina antes mostramos sus datos
                      $announced=true;
                      $msg="** ------------------ REAPROVISIONAMIENTO EXPENDEDORA ".$machine->getName()." (GESTOR ".$machine->getManager()->getName().") ------------------ **";
                      file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$machine->getManager()->getDiscordchannel().'&msg='.urlencode($msg));
                  }
                  $msg="Canal: **".$channel->getName()."** Ref: **".$channel->getProductcode()."** - ".$channel->getProductname()." - Cantidad: **".($channel->getMaxquantity()-$channel->getQuantity()." unidades.**");
                  file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$machine->getManager()->getDiscordchannel().'&msg='.urlencode($msg));
                }
              }
              if($announced){
                $msg="** ------------------ FIN EXPENDEDORA ".$machine->getName()." (GESTOR ".$machine->getManager()->getName().") ------------------ **";
                file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$machine->getManager()->getDiscordchannel().'&msg='.urlencode($msg));
              }

          }
        }

  }
}
?>
