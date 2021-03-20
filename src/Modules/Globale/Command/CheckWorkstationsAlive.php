<?php
namespace App\Modules\Globale\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleWorkstations;


class CheckWorkstationsAlive extends ContainerAwareCommand
{
  private $doctrine;
  private $entityManager;
  protected function configure(){
        $this
            ->setName('globale:checkworkstationsalive')
            ->setDescription('Check if workstations are alive')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();

    //Execute different tasks
    $output->writeln([__DIR__]);
    $this->CheckStatus($output);
  }

  function CheckStatus($output){

    $companiesRepository = $this->doctrine->getRepository(GlobaleCompanies::class);
    $workstationsRepository = $this->doctrine->getRepository(GlobaleWorkstations::class);
    $workstations=$workstationsRepository->findBy(["deleted"=>0, "active"=>1]);
    foreach($workstations as $key=>$item){
      $alive=false;
      if($item->getIpaddress()){
        $output->writeln([' -Cheking icmp: '.$item->getIpaddress()]);
        $exec = exec("ping -c 2 -s 64 -t 64 ".$item->getIpaddress());
        $array=explode("=", $exec );
        $output->writeln([end($array)]);
        $array = explode("/", end($array) );
        if(is_array($array) && count($array)>1 && $array[1]>0) $alive=true;
      }
      if($item->getAlive()!=$alive){
          if($alive) file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?msg=".urlencode(":computer: El equipo ".$item->getName()." está encendido :green_circle:"));
            else file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?msg=".urlencode(":computer: El equipo ".$item->getName()." se ha apagado :red_circle:"));
          $item->setAlive($alive);
          $this->entityManager->persist($item);
          $this->entityManager->flush();
      }

    }
  }


}
?>
