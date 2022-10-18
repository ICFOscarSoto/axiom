<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesLogs;
use App\Modules\Globale\Entity\GlobaleCompanies;



class StoresManagersVendingMachinesCheckConnectionLost extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:storesManagersVendingMachinesCheckConnectionLost')
            ->setDescription('Comprobar conectividad con las expendedoras')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $usersRepository=$doctrine->getRepository(GlobaleUsers::class);
        $repositoryVendingMachines = $doctrine->getManager()->getRepository(ERPStoresManagersVendingMachines::class);

        $output->writeln('');
        $output->writeln('COMPROBANDO CONECTIVIDAD DE MAQUINAS EXPENDEDORAS');
        $output->writeln('===================================================');

        $machines = $repositoryVendingMachines->getConnectionLostVendingMachines();

        foreach($machines as $machine){
          $machine=$repositoryVendingMachines->find($machine['id']);
          $type=2;
      		$description='Conexión perdida. Ultimo contacto el '.$machine->getLastcheck()->format('d/m/Y').' a las '.$machine->getLastcheck()->format('H:i:s');
      		$vendingMachineLog= new ERPStoresManagersVendingMachinesLogs();
      		$vendingMachineLog->setVendingmachine($machine);
      		$vendingMachineLog->setType($type);
      		$vendingMachineLog->setDescription($description);
      		$vendingMachineLog->setDateadd(new \DateTime());
      		$vendingMachineLog->setDateupd(new \DateTime());
      		$vendingMachineLog->setActive(1);
      		$vendingMachineLog->setDeleted(0);
      		$doctrine->getManager()->persist($vendingMachineLog);
      		$doctrine->getManager()->flush();
      		if($type==2){
      				if($machine->getAlertnotifyaddress()!=null){
      					file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$machine->getAlertnotifyaddress().'&msg='.urlencode('Máquina '.$machine->getName().': '.$description));
      					sleep(1);
      				}
      		}
          $machine->setConnectionlostnotified(true);
          $doctrine->getManager()->persist($machine);
      		$doctrine->getManager()->flush();
        }

  }
}
?>
