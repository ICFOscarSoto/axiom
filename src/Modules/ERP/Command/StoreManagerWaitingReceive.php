<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\Navision\Entity\NavisionTransfers;
use App\Modules\Globale\Entity\GlobaleCompanies;



class StoreManagerWaitingReceive extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:waitingReceive')
            ->setDescription('Recordatorio de transpasos pendientes de recibir')
            ->addArgument('var_manager', InputArgument::REQUIRED, 'Gestor sobre el que informar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    $var_manager = $input->getArgument('var_manager');
    $storesRepository=$doctrine->getRepository(ERPStores::class);
    $transfersRepository=$doctrine->getRepository(NavisionTransfers::class);
    $managerepository=$doctrine->getRepository(ERPStoresManagers::class);

    $output->writeln('');
    $output->writeln('Enviando avisos de traspasos pendientes de recibir');
    $output->writeln('===================================================');

    $unreceivedTransfers=$storesRepository->getUnreceivedTransfers($var_manager);
    $manager=$managerepository->findOneBy(["name"=>$var_manager]);

    foreach ($unreceivedTransfers as $name){
      /* Enviamos avisos al canal de discord con los traspasos que faltan */
      $transfer=$transfersRepository->findOneBy(["name"=>'22TR03626']);
      $msg="El traspaso ".$transfer->getName()." esta pendiente de recibir en el almacen ".$transfer->getDestinationstore()->getName()." desde que se envío el día ".date_format($transfer->getDateadd(), "d/m/Y");
      if ($manager->getIncidentchannel()!=null) file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$manager->getIncidentchannel().'&msg='.urlencode($msg));
      sleep(1);
      /* Enviamos por correo los traspasos que faltan */
      $msg="El traspaso ".$transfer->getName()." esta pendiente de recibir en el almacen ".$transfer->getDestinationstore()->getName()." desde que se envío el día ".date_format($transfer->getDateadd(), "d/m/Y");
      $msg.="Puedes descargar el traspaso en el siguiente enlace <a href='https://axiom.ferreteriacampollano.com/es/ERP/generateQR?hash=".$transfer->getHash()."&userId=".$transfer->getDestinationstore()->getManagedBy()->getUser()->getId()."'>descargar aquí</a>.";
      $msg.="</ul><br/><br/>Por favor, no responda a este mensaje, ha sido generado de forma automática. Si desea ponerse en contacto con nosotros para comentarnos alguna incidencia o mejora de este servicio, por favor, contacte con su comercial o escríbanos a <a href='mailto:sistemas@ferreteriacampollano.com'>sistemas@ferreteriacampollano.com</a>.";
      $postdata = http_build_query([
              'from' => '14',
              'to' => $transfer->getDestinationstore()->getTransfernotifyaddress(),
              'cc' => '',
              'bcc' =>'',
              'subject' =>'Traspaso '.$transfer->getName().' pendiente de recepcionar en '.$transfer->getDestinationstore()->getName(),
              'files' => '{}',
              'text_content' =>'',
              'html_content' => $msg
            ]);
      $opts = ["http" => [
                  "method" => "POST",
                  "header" => "Content-Type: application/x-www-form-urlencoded\r\nX-AUTH-TOKEN: a052732c94ac72ea4ec62ad9b2b61910ea4cb1448f2302895c871ba88cc6f5a2130f5686f6c181df81f69b6b3c354037389ec7a388f86798a98cb914bcd19bb528538d2cc796a9e47130883f57ceb4ffeeb32dc99a4cccb4fbb02f33be53e1765781ac611c48590c5b5e0915c82bff1952575ed750966f54adbc8074f3ad41fe92feb85affe60698f73088fc7afb5c77037863ccf6836c621304492888fcfbd7919c5e80181155ed3a0ea95421a6a990ab01f89aca9f9a4c22d9cbfa6d2729932060f597960e8683c5d3015060868b5bf6d39f0fd50cda7e\r\nX-AUTH-DOMAIN: ferreteriacampollano.com",
                  "content" => $postdata
                ]
              ];
      $context = stream_context_create($opts);
      $output->writeln("  - Enviando email de traspasos no recibidos");
      $file = file_get_contents('https://axiom.ferreteriacampollano.com/api/emails/send', false, $context);
    }
  }
}
?>
