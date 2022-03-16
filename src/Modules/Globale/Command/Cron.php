<?php
namespace App\Modules\Globale\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRAutoCloseClocks;
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\HR\Entity\HRWorkCenters;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Globale\Entity\GlobaleUserSessions;
use App\Modules\Globale\Entity\GlobaleUsers;

class Cron extends ContainerAwareCommand
{
  private $doctrine;
  private $entityManager;
  protected function configure(){
        $this
            ->setName('globale:cron')
            ->setDescription('Programmed tasks of Axiom')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();

    //Execute different tasks
    $this->HRAutoCloseClocks($output);
    $this->deleteSessionsWithoutData($output);
  }
  function GlobaleCalculateDiskUsage(){


  }

  function deleteSessionsWithoutData($output){
    $sessionsRepository = $this->doctrine->getRepository(GlobaleUserSessions::class);
    $sessions=$sessionsRepository->findBy(["data"=>'']);
    foreach($sessions as $session){
      $this->entityManager->remove($session);
    }
    $this->entityManager->flush();
  }

  function HRAutoCloseClocks($output){
    $autoCloseclocksRepository = $this->doctrine->getRepository(HRAutoCloseClocks::class);
    $clocksRepository = $this->doctrine->getRepository(HRClocks::class);
    $CompaniesRepository = $this->doctrine->getRepository(GlobaleCompanies::class);
    $usersRepository = $this->doctrine->getRepository(GlobaleUsers::class);
    $msgRH="";
    $workersIds=[];
    $HRResponsables=[7,10];
    //Search AutoCloseClocks at now time
    $autoCloseclocks=$autoCloseclocksRepository->findByTime(date('H:i'));
    $output->writeln([
            'Searching Auto Close Clocks',
            '===========================',
            '',
    ]);
    foreach($autoCloseclocks as $key=>$item){
      $company=$CompaniesRepository->find($item["company_id"]);
      $output->writeln(['- '.$company->getName().":"]);

      //Search Open Clocks of this company
      $clocks=$clocksRepository->findOpenByCompany($company->getId(), $item["workcenter_id"], $item["department_id"]);
      foreach($clocks as $keyClock=>$itemClock){
        $output->writeln(['   - '.$itemClock["lastname"].", ".$itemClock["name"]]);
        $clock_element=$clocksRepository->find($itemClock["id"]);
        //Close Clocks and mark as incidence
        $clock_element->setInvalid(1);
        $clock_element->setEnd(new \DateTime());
        $this->entityManager->persist($clock_element);
        $this->entityManager->flush();
        //Create a history of changes
        $history=new GlobaleHistories();
        $history->setEntity(get_class($clock_element));
        $history->setEntityId($clock_element->getId());
        $history->setCompany($company);
        $history->setDateadd(new \DateTime());
        $history->setDateupd(new \DateTime());
        $history->setChanges(json_encode([["attribute"=>"invalid", "oldvalue"=>0, "newvalue"=>1],
                                          ["attribute"=>"end", "oldvalue"=>null, "newvalue"=>date('Y-m-d H:i:s')]]));
        $history->setActive(TRUE);
        $history->setDeleted(FALSE);
        $this->entityManager->persist($history);
        $this->entityManager->flush();
        //Send notifications
        if($company->getId()!=2) continue;
        if(!in_array($clock_element->getWorker()->getId(), $workersIds)){
          if($clock_element->getWorker()->getUser()!=null && $clock_element->getWorker()->getUser()->getDiscordchannel()!=null && $clock_element->getWorker()->getUser()->getId()==7){
            $msg="Tu jornada laboral ha sido cerrada automáticamente y marcada como ** INCIDENCIA**, para solucionar el problema ponte en contacto con el responsable de \"Recursos Humanos\"";
            $output->writeln(['https://icfbot.ferreteriacampollano.com/message.php?channel='.$clock_element->getWorker()->getUser()->getDiscordchannel().'&msg='.urlencode($msg)]);
            file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$clock_element->getWorker()->getUser()->getDiscordchannel().'&msg='.urlencode($msg));
          }
          $workersIds[]=$clock_element->getWorker()->getId();
        }

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
          $output->writeln(['https://icfbot.ferreteriacampollano.com/message.php?channel='.$user->getDiscordchannel().'&msg='.urlencode($msgRH)]);
          file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$user->getDiscordchannel().'&msg='.urlencode($msgRH));
        }
      }
    }



  }
}
?>
