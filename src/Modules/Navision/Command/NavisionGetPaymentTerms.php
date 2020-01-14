<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPPaymentTerms;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetPaymentTerms extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getpaymentterms')
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
      case 'paymentterms': $this->importCustomer($input, $output);
      break;
      case 'all':
        $this->importPaymentTerms($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importPaymentTerms(InputInterface $input, OutputInterface $output){
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"paymentterms"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("paymentterms");
       $navisionSync->setLastsync(new \DateTime("@0"));
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando términos de pago....');
     $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPaymentTerms.php?from='.$navisionSync->getMaxtimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     //dump($products["products"]);
     $repository=$this->doctrine->getRepository(ERPPaymentTerms::class);

     //Disable SQL logger
     $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

     foreach ($objects["class"] as $key=>$object){
       $output->writeln('  - '.$object["code"].' - '.$object["name"]);
       $obj=$repository->findOneBy(["code"=>$object["code"]]);
       if ($obj==null) {
         $obj=new ERPPaymentTerms();
         $obj->setCode($object["code"]);
         $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
         $company=$repositoryCompanies->find(2);
         $obj->setCompany($company);
         $obj->setDateadd(new \Datetime());
         $obj->setDeleted(0);
         $obj->setActive(1);
       }


        $obj->setName($object["name"]);
        $obj->setFirstexpiration($object["firstexpiration"]);
        $obj->setDateupd(new \Datetime());
        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();
        $this->doctrine->getManager()->clear();

     }
     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
     $this->doctrine->getManager()->persist($navisionSync);
     $this->doctrine->getManager()->flush();
   }


}
?>
