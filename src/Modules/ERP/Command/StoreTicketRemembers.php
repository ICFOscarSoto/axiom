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
use App\Modules\ERP\Entity\ERPStoreTickets;
use App\Modules\Globale\Entity\GlobaleCompanies;

class StoreTicketRemembers extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:storeTicketsRemembers')
            ->setDescription('Recordatorios de Incidencias de Almacén')
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
    $storeTicketsRepository=$doctrine->getRepository(ERPStoreTickets::class);
    $companiesrepository=$doctrine->getRepository(GlobaleCompanies::class);
    $output->writeln('');
    $output->writeln('Obteniendo Incidencias con recordatorios pendientes');
    $output->writeln('===================================================');
    $remembers=$storeTicketsRepository->getNotificationsTickets();
    foreach($remembers as $remember){
      $storeticket=$storeTicketsRepository->findOneBy(["id"=>$remember["id"], "active"=>1, "deleted"=>0]);
      $output->writeln('- ['.$remember["id"].']');
      if($storeticket->getAgent()){
        //Send notification to agent
        $channel=$storeticket->getAgent()->getDiscordchannel();
        if(!$channel){ $output->writeln('   - El usuario no tiene canal de Discord'); continue; }
        //Check if the user has a worker associated
        $worker=$workersRepository->findOneBy(["user"=>$storeticket->getAgent(), "active"=>1, "deleted"=>0]);
        if($worker){
            //Check if worker is working now
            if(!$workersRepository->isWorking($worker)){
              $output->writeln('   - El trabajador no esta trabajando ahora');
              continue;
            }
        }
        $msg="¡Recuerda!, la incidencia Nº **".$storeticket->getCode()."** sigue pendiente de solución desde el **".$storeticket->getDateadd()->format("d/m/Y")."** a las **".$storeticket->getDateadd()->format("H:i")."**. Su estado es **".$storeticket->getStoreticketstate()->getName()."**, por favor si esta finalizada márcala como **Solucionada** lo antes posible\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/storetickets/form/'.$storeticket->getId();
        file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
        $output->writeln('   - Notificación recordatorio enviada al usuario');
        $storeticket->setDatelastnotify(new \DateTime());
        $entityManager->persist($storeticket);
        $entityManager->flush();
      }else if($storeticket->getDepartment()){
        //Send notification to department
        $channel=$storeticket->getDepartment()->getDiscordchannel();
        if(!$channel){ $output->writeln('   - El departamento no tiene canal de Discord'); continue;}
        //Check if some worker of department is working now
        $working_now=false;
        $workers=$workersRepository->findBy(["department"=>$storeticket->getDepartment(), "active"=>1, "deleted"=>0]);
        foreach($workers as $worker){
          if($workersRepository->isWorking($worker)) $working_now=true;
        }
        if(!$working_now){ $output->writeln('   - Ningún trabajador del departamento esta trabajando ahora'); continue;}
        $msg="¡Recordad!, la incidencia Nº **".$storeticket->getCode()."** sigue pendiente de solución desde el **".$storeticket->getDateadd()->format("d/m/Y")."** a las **".$storeticket->getDateadd()->format("H:i")."**. Su estado es **".$storeticket->getStoreticketstate()->getName()."**, por favor si esta finalizada marcadla como **Solucionada** lo antes posible\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/storetickets/form/'.$storeticket->getId();
        file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
        $output->writeln('   - Notificación recordatorio enviada al departamento');
        $storeticket->setDatelastnotify(new \DateTime());
        $entityManager->persist($storeticket);
        $entityManager->flush();
      }
    }
  }
}
?>
