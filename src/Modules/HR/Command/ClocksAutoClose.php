<?php
namespace App\Modules\HR\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Globale\Entity\GlobaleUsers;

class ClocksAutoClose extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('HR:clocksAutoClose')
            ->setDescription('Cerrar fichajes fuera de hora')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $HRResponsables=[7,10];
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    $clocksRepository = $doctrine->getRepository(HRClocks::class);
    $workersRepository = $doctrine->getRepository(HRWorkers::class);
    $usersRepository = $doctrine->getRepository(GlobaleUsers::class);
    $msgRH="";
    $workersIds=[];
    $clocks=$clocksRepository->findBy(["end"=>null]);
    $output->writeln([
            'Clocks auto close',
            '=================',
            '',
    ]);
    foreach($clocks as $key=>$val){
     if($val->getWorker()->getCompany()->getId()!=2) continue;
     $output->writeln(['- '.$val->getWorker()->getName().' '.$val->getWorker()->getLastname()]);
     $val->setEnd(new \DateTime());
     $val->setInvalid(1);
     $val->setDateupd(new \DateTime());
     $val->setTime(date_timestamp_get($val->getEnd())-date_timestamp_get($val->getStart()));
     $entityManager->persist($val);
     $entityManager->flush();

     //Notify Worker
     if(!in_array($val->getWorker()->getId(), $workersIds)){
       if($val->getWorker()->getUser()!=null && $val->getWorker()->getUser()->getDiscordchannel()!=null && $val->getWorker()->getUser()->getId()==7){
         $msg="Tu jornada laboral ha sido cerrada automáticamente y marcada como ** INCIDENCIA**, para solucionar el problema ponte en contacto con el responsable de \"Recursos Humanos\"";
         file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$val->getWorker()->getUser()->getDiscordchannel().'&msg='.urlencode($msg));
       }
       $workersIds[]=$val->getWorker()->getId();
     }
    }

    //Notify HHRR Responsable
    if(count($workersIds)){
      foreach($HRResponsables as $iduser){
        $user=$usersRepository->find($iduser);
        if(!$user) continue;
        $msgRH="Los siguientes trabajadores no han cerrado su jornada laboral y se han marcado como ** INCIDENCIA **: \n".$msgRH;
        file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$user->getDiscordchannel().'&msg='.urlencode($msgRH));
        foreach($workersIds as $idowrker){
          $worker=$workersRepository->find($idowrker);
          if(!$worker) continue;
          $msgRH="  \n   - ".$worker->getLastname().', '.$worker->getName()." -> https://axiom.ferreteriacampollano.com/es/HR/workers/form/".$worker->getId()."?tab=clocks";
          file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$user->getDiscordchannel().'&msg='.urlencode($msgRH));
        }
      }
    }
  }
}
?>
