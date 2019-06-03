<?php
namespace App\Modules\HR\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\HR\Entity\HRClocks;

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
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    $clocksRepository = $doctrine->getRepository(HRClocks::class);
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
    }

  }
}
?>
