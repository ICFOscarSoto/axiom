<?php
namespace App\Modules\HR\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Globale\Entity\GlobaleCompanies;

class ChangeVoipStatus extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('HR:changeVoipStatus')
            ->setDescription('Cambiar estado en central VoIP')
            ->addArgument('worker', InputArgument::REQUIRED, '¿Trabajador afectado?')
            ->addArgument('type', InputArgument::REQUIRED, '¿Nuevo estado de la central?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    $id = $input->getArgument('worker');
    $type = $input->getArgument('type');
    $workersrepository=$doctrine->getRepository(HRWorkers::class);
    $companiesrepository=$doctrine->getRepository(GlobaleCompanies::class);

    $worker=$workersrepository->findOneBy(["id"=>$id, "deleted"=>0, "active"=>1]);
    if(!$worker) {$output->writeln('* Trabajador no encontrado.');exit;}
    if(!$worker->getVoipregister() || !$worker->getVoippass() || !$worker->getExtension())  {$output->writeln('* VoIp no configurada para el trabajador.'); exit;}
    $company=$companiesrepository->findOneBy(["id"=>$worker->getCompany()->getId(), "deleted"=>0, "active"=>1]);
    if(!$company) {$output->writeln('* Empresa no encontrada.');exit;}
    if(!$company->getVoipaddress() || !$company->getVoipregistercode() || !$company->getVoipunregistercode()) {$output->writeln('* VoIp no configurada para la empresa.'); exit;}
    $output->writeln("screen -d -m -S pjsua".$worker->getExtension().$type==1?"in":"out"." /home/operador/pjproject-2.6/pjsip-apps/bin/pjsua-x86_64-unknown-linux-gnu --id=sip:".$worker->getExtension()."@".$company->getVoipaddress()." --registrar=sip:".$company->getVoipaddress()." --local-port=3037 --username=".$worker->getExtension()." --password=".$worker->getVoippass()." --null-audio --no-tcp --realm=* sip:".$type==1?$company->getVoipregistercode():$company->getVoipunregistercode()."@".$company->getVoipaddress());
    shell_exec("screen -d -m -S pjsua".$worker->getExtension().$type==1?"in":"out"." /home/operador/pjproject-2.6/pjsip-apps/bin/pjsua-x86_64-unknown-linux-gnu --id=sip:".$worker->getExtension()."@".$company->getVoipaddress()." --registrar=sip:".$company->getVoipaddress()." --local-port=3037 --username=".$worker->getExtension()." --password=".$worker->getVoippass()." --null-audio --no-tcp --realm=* sip:".$type==1?$company->getVoipregistercode():$company->getVoipunregistercode()."@".$company->getVoipaddress());
    sleep(10);
    shell_exec("screen -S pjsua".$worker->getExtension().$type==1?"in":"out"." -X quit");
    $output->writeln(' * Estado de VoIP cambiado');

  }
}
?>
