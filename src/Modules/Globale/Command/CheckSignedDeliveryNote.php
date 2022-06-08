<?php
namespace App\Modules\Globale\Command;

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

class CheckSignedDeliveryNote extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('signeddeliverynote:check')
            ->setDescription('Recordatorios de albaranes de venta no digitalizados')
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
    $output->writeln('Obteniendo albaranes de ventas que deberian estar firmados y digitalizados');
    $output->writeln('==========================================================================');
    $date=new \DateTime("2022-06-07");
    $destDir=__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'ERPSignedDeliveryNotes'.DIRECTORY_SEPARATOR;
    $destDir=$destDir.$date->format('Y').DIRECTORY_SEPARATOR.$date->format('m').DIRECTORY_SEPARATOR.$date->format('d').DIRECTORY_SEPARATOR;
    $json=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-getSignedDeliveryNotes.php?date='.urlencode($date->format('d/m/Y')));
    //dump($json);
    $deliverynotes=json_decode($json, true);
    foreach($deliverynotes as $key => $deliverynote) {
        $filename=$date->format('Y').'-'.$date->format('m').'-'.$date->format('d').' - '.$deliverynote["number"].'.pdf';
        if(!file_exists($destDir.$filename) || !is_file($destDir.$filename)){
            $output->writeln($deliverynote["number"]);
        }

    }

    $remembers=$storeTicketsRepository->getNotificationsTickets();

  }
}
?>
