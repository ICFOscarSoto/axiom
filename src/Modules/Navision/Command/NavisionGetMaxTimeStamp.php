<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetMaxTimeStamp extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";
  private $discordchannel="883046233017552956";

  protected function configure(){
        $this
            ->setName('navision:getMaxtimestamp')
            ->setDescription('Sync navision principal entities')
            ->addArgument('entity', InputArgument::REQUIRED, '¿Entidad que sincronizar?')
        ;
  }
  protected function execute(InputInterface $input, OutputInterface $output)
  {

    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    $entity = $input->getArgument('entity');
    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $this->company=$repositoryCompanies->find(2);
    $output->writeln('');
    $output->writeln('Comenzando sincronizacion Navision');
    $output->writeln('==================================');
    switch($entity){
      case 'maxtimestamp': {
        $this->maxtimestamp($input, $output);
        break;
      }
      default:
        $output->writeln('Opcion no válida');
      break;
    }
  }

  public function maxtimestamp(InputInterface $input, OutputInterface $output){

    $this->updateTimestamp($input, $output, "products");
    $this->updateTimestamp($input, $output, "References");
    $this->updateTimestamp($input, $output, "EAN13");



  }

private function updateTimestamp(InputInterface $input, OutputInterface $output, $name){
  $datetime=new \DateTime();
  $output->writeln('* Obteniendo el Maxtimestamp de los '.$name.' ....');
  $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getmaxtimestamp.php?name='.$name);
  $objects=json_decode($json, true);
  $objects=$objects[0];
  $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
  if ((int)$objects["maxtimestamp"]!=0) {
    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>$name]);
    $navisionSync->setMaxtimestamp((int)$objects["maxtimestamp"]);
    $navisionSync->setLastsync($datetime);
    $this->doctrine->getManager()->persist($navisionSync);
  }
  $this->doctrine->getManager()->flush();
}


}
