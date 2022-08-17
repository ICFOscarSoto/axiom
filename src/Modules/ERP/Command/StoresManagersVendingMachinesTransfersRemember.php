<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\Globale\Entity\GlobaleCompanies;



class StoresManagersVendingMachinesTransfersRemember extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:storesManagersVendingMachinesTransfers')
            ->setDescription('Recordatorio de transferencias de maquinas expendedoras')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();
        $usersRepository=$doctrine->getRepository(GlobaleUsers::class);
        $vendingMachinesRepository=$doctrine->getRepository(ERPStoresManagersVendingMachines::class);
        $vendingMachinesChannelsRepository=$doctrine->getRepository(ERPStoresManagersVendingMachinesChannels::class);

        $output->writeln('');
        $output->writeln('TRASPASOS A MAQUINAS EXPENDEDORAS');
        $output->writeln('===================================================');

        $machines = $vendingMachinesRepository->findBy(["active"=>1, "deleted"=>0]);

        foreach($machines as $machine){
          $announced=false;
          //Notificaciones por DISCORD
          if($machine->getManager() && $machine->getReplenishmentnotifytype()==0 && $machine->getReplenishmentnotifyaddress()!="" && $machine->getManager()->getActive() && !$machine->getManager()->getDeleted()){
              $output->writeln("  - Comprobando máquina ".$machine->getName());
              $channels=$vendingMachinesChannelsRepository->findBy(["vendingmachine"=> $machine,"active"=>1, "deleted"=>0]);
              foreach($channels as $channel){
                if(($channel->getQuantity()<=$channel->getMinquantity()) && $channel->getProductcode()!=null){
                  //Comunicamos la cantidad que hay que reaprovisionar en esta maquina
                  if(!$announced){
                      //Si no hemos mostrado otro reaprovisionamiento de esta maquina antes mostramos sus datos
                      $announced=true;
                      $msg="** ------------------ REAPROVISIONAMIENTO EXPENDEDORA ".$machine->getName()." (GESTOR ".$machine->getManager()->getName().") ------------------ **";
                      file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$machine->getReplenishmentnotifyaddress().'&msg='.urlencode($msg));
                      sleep(1);
                  }
                  $msg="Canal: **".$channel->getName()."** Ref: **".$channel->getProductcode()."** - ".$channel->getProductname()." - Cantidad: **".(floor(($channel->getMaxquantity()-$channel->getQuantity())/$channel->getMultiplier())." unidades.**");
                  file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$machine->getReplenishmentnotifyaddress().'&msg='.urlencode($msg));
                  sleep(1);
                }
              }
              if($announced){
                $msg="** ------------------ FIN EXPENDEDORA ".$machine->getName()." (GESTOR ".$machine->getManager()->getName().") ------------------ **";
                file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$machine->getReplenishmentnotifyaddress().'&msg='.urlencode($msg));
                sleep(1);
              }
          }
          //Notificaciones por correo electronico
          if($machine->getManager() && $machine->getReplenishmentnotifytype()==1 && $machine->getReplenishmentnotifyaddress()!="" && $machine->getManager()->getActive() && !$machine->getManager()->getDeleted()){
              $output->writeln("  - Comprobando máquina ".$machine->getName());
              $channels=$vendingMachinesChannelsRepository->findBy(["vendingmachine"=> $machine,"active"=>1, "deleted"=>0]);
              $msg="A continuación se detallan los canales a reaprovisionar en la máquina <b>".$machine->getName()."</b>: <br/><br/>";
              $send=false;
              $msg.="<ul>";
              foreach($channels as $channel){
                if(($channel->getQuantity()<=$channel->getMinquantity()) && $channel->getProductcode()!=null){
                  //Comunicamos la cantidad que hay que reaprovisionar en esta maquina
                  $msg.="<li>Canal: <b>".$channel->getName()."</b> (Num. canal: <b>".str_pad($channel->getChannel(),3,'0',STR_PAD_LEFT)."</b>) Ref: <b>".$channel->getProductcode()."</b> - ".$channel->getProductname()." - Cantidad: <b>".(floor(($channel->getMaxquantity()-$channel->getQuantity())/$channel->getMultiplier())." unidades.</b></li>");
                  $send=true;
                }
              }
              $msg.="</ul><br/><br/>Por favor, no responda a este mensaje, ha sido generado de forma automática. Si desea ponerse en contacto con nosotros para comentarnos alguna incidencia o mejora de este servicio, por favor, contacte con su comercial o escríbanos a <a href='mailto:sistemas@ferreteriacampollano.com'>sistemas@ferreteriacampollano.com</a>.";

              if($send){
                $postdata = http_build_query([
                        'from' => '14',
                        'to' => $machine->getReplenishmentnotifyaddress(),
                        'cc' => '',
                        'bcc' =>'',
                        'subject' =>'Reaprovisionamiento máquina '.$machine->getName(),
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
                $output->writeln("  - Enviando email de reaprovisionamiento");
                $file = file_get_contents('https://axiom.ferreteriacampollano.com/api/emails/send', false, $context);
              }
          }


        }

  }
}
?>
