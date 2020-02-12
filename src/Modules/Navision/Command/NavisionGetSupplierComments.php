<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPSupplierCommentLines;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetSupplierComments extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getsuppliercomments')
            ->setDescription('Sync navision principal entities')
            ->addArgument('entity', InputArgument::REQUIRED, '¿Entidad que sincronizar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    $entity = $input->getArgument('entity');

    //repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
  // $this->company=$repositoryCompanies->find(2);

    $output->writeln('');
    $output->writeln('Comenzando sincronizacion Navision');
    $output->writeln('==================================');
    switch($entity){
      case 'suppliercomments': $this->importCustomerComment($input, $output);
      break;
      case 'all':
        $this->importSupplierComment($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importSupplierComment(InputInterface $input, OutputInterface $output){
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"suppliercomments"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando comentarios de proveedores....');
     $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getComments.php?type=2&from='.$navisionSync->getMaxtimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     $repositorySuppliers=$this->doctrine->getRepository(ERPSuppliers::class);
     $repository=$this->doctrine->getRepository(ERPSupplierCommentLines::class);
     //Disable SQL logger
     $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

     foreach ($objects["class"] as $key=>$object){
       $supplier=$repositorySuppliers->findOneBy(["code"=>$object["entity"]]);
       if($object["comment"]!="" AND $supplier!=NULL)
       {
         $output->writeln('  - '.$object["entity"]);
         $obj=$repository->findOneBy(["comment"=>$object["comment"]]);
         if ($obj==null) {
           $obj=new ERPSupplierCommentLines();
           //$company=$repositoryCompanies->find(2);
           //$obj->setCompany($company);
           $obj->setComment($object["comment"]);
           $datetime=new \DateTime(date('Y-m-d 00:00:00',strtotime($object["date"]["date"])));
          // dump(date('Y-m-d 00:00:00',strtotime($object["date"]["date"])));
           $obj->setDateadd($datetime);

           $obj->setSupplier($supplier);
           $obj->setDeleted(0);
           $obj->setActive(1);
           $obj->setDateupd($datetime);
           $this->doctrine->getManager()->persist($obj);
           $this->doctrine->getManager()->flush();
           $this->doctrine->getManager()->clear();
         }
     }

     }
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"suppliercomments"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setEntity("suppliercomments");
     }
     $navisionSync->setLastsync($datetime);
     $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
     $this->doctrine->getManager()->persist($navisionSync);
     $this->doctrine->getManager()->flush();
   }


}
?>
