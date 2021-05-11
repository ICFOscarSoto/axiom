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
use App\Modules\Globale\Entity\GlobaleCompanies;

class SalesTicketRemembers extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:salesTicketsRemembers')
            ->setDescription('Recordatorios de Incidencias de Ventas')
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
    $salesTicketsRepository=$doctrine->getRepository(ERPSalesTickets::class);
    $companiesrepository=$doctrine->getRepository(GlobaleCompanies::class);
    $output->writeln('');
    $output->writeln('Obteniendo Incidencias con recordatorios pendientes');
    $output->writeln('===================================================');
    $remembers=$salesTicketsRepository->getNotificationsTickets();
    foreach($remembers as $remember){
      $salesticket=$salesTicketsRepository->findOneBy(["id"=>$remember["id"], "active"=>1, "deleted"=>0]);
      $output->writeln('- ['.$remember["id"].']');
      if($salesticket->getAgent()){
        //Send notification to agent
        $channel=$salesticket->getAgent()->getDiscordchannel();
        if(!$channel){ $output->writeln('   - El usuario no tiene canal de Discord'); continue; }
        //Check if the user has a worker associated
        $worker=$workersRepository->findOneBy(["user"=>$salesticket->getAgent(), "active"=>1, "deleted"=>0]);
        if($worker){
            //Check if worker is working now
            if(!$workersRepository->isWorking($worker)){
              $output->writeln('   - El trabajador no esta trabajando ahora');
              continue;
            }
        }
        $msg="¡Recuerda!, la incidencia Nº **".$salesticket->getCode()."** sigue pendiente de solución desde el **".$salesticket->getDateadd()->format("d/m/Y")."** a las **".$salesticket->getDateadd()->format("H:i")."**. Su estado es **".$salesticket->getSalesticketstate()->getName()."**, por favor si esta finalizada márcala como **Solucionada** lo antes posible\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$salesticket->getId();
        file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
        $output->writeln('   - Notificación recordatorio enviada al usuario');
        $salesticket->setDatelastnotify(new \DateTime());
        $entityManager->persist($salesticket);
        $entityManager->flush();
      }else if($salesticket->getDepartment()){
        //Send notification to department
        $channel=$salesticket->getDepartment()->getDiscordchannel();
        if(!$channel){ $output->writeln('   - El departamento no tiene canal de Discord'); continue;}
        //Check if some worker of department is working now
        $working_now=false;
        $workers=$workersRepository->findBy(["department"=>$salesticket->getDepartment(), "active"=>1, "deleted"=>0]);
        foreach($workers as $worker){
          if($workersRepository->isWorking($worker)) $working_now=true;
        }
        if(!$working_now){ $output->writeln('   - Ningún trabajador del departamento esta trabajando ahora'); continue;}
        $msg="¡Recordad!, la incidencia Nº **".$salesticket->getCode()."** sigue pendiente de solución desde el **".$salesticket->getDateadd()->format("d/m/Y")."** a las **".$salesticket->getDateadd()->format("H:i")."**. Su estado es **".$salesticket->getSalesticketstate()->getName()."**, por favor si esta finalizada marcadla como **Solucionada** lo antes posible\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$salesticket->getId();
        file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
        $output->writeln('   - Notificación recordatorio enviada al departamento');
        $salesticket->setDatelastnotify(new \DateTime());
        $entityManager->persist($salesticket);
        $entityManager->flush();
      }
    }
  }
}
?>
