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
  }
  function GlobaleCalculateDiskUsage(){


  }

  function HRAutoCloseClocks($output){
    $autoCloseclocksRepository = $this->doctrine->getRepository(HRAutoCloseClocks::class);
    $clocksRepository = $this->doctrine->getRepository(HRClocks::class);
    $CompaniesRepository = $this->doctrine->getRepository(GlobaleCompanies::class);

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




      }
    }

  }
}
?>
