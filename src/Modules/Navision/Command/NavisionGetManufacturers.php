<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetManufacturers extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getmanufacturers')
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
      case 'manufacturers': $this->importManufacturers($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

   public function importManufacturers(InputInterface $input, OutputInterface $output){
     //------   Create Lock Mutex    ------
     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
         $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetManufacturers-importManufacturers.lock', 'c');
     } else {
        $fp = fopen('/tmp/axiom-navisionGetManufacturers-importManufacturers.lock', 'c');
     }
     if (!flock($fp, LOCK_EX | LOCK_NB)) {
       $output->writeln('* Fallo al iniciar la sincronizacion de marcas: El proceso ya esta en ejecución.');
       exit;
     }

     //------   Critical Section START   ------
     $repositoryCountries=$this->doctrine->getRepository(GlobaleCountries::class);
     $repositoryStates=$this->doctrine->getRepository(GlobaleStates::class);
     $repository=$this->doctrine->getRepository(ERPManufacturers::class);
     $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
     $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"manufacturers"]);
     if ($navisionSync==null) {
       $navisionSync=new NavisionSync();
       $navisionSync->setMaxtimestamp(0);
     }
     $datetime=new \DateTime();
     $output->writeln('* Sincronizando marcas....');

     //Disable SQL logger
     $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
     $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getManufacturers.php?from='.$navisionSync->getMaxtimestamp());
     $objects=json_decode($json, true);
     $objects=$objects[0];
     foreach ($objects["class"] as $key=>$object){

       $output->writeln("Vamos a analizar la marca ".$object["code"]);
       $obj=$repository->findOneBy(["code"=>$object["code"]]);
       if ($obj==null) {

         $obj=new ERPManufacturers();
         $marca_new=str_replace(" ","",($object["code"]));
			   if($marca_new=="BONFIGLI") $marca_new="BONFIGLIOLI";
			   if($marca_new=="JPANADERO") $marca_new="JUAN PANADERO";
         $obj->setCode($marca_new);
         $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
         $company=$repositoryCompanies->find(2);
         $obj->setCompany($company);
         $obj->setDateadd(new \Datetime());
         $obj->setDeleted(0);
         $obj->setActive(1);
       }

        $obj->setName($object["description"]);
        $obj->setDateupd(new \Datetime());

        $this->doctrine->getManager()->persist($obj);
        $this->doctrine->getManager()->flush();
      }


   $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"manufacturers"]);
   if ($navisionSync==null) {
     $navisionSync=new NavisionSync();
     $navisionSync->setEntity("manufacturers");
   }
   $navisionSync->setLastsync($datetime);
   $navisionSync->setMaxtimestamp($objects["maxtimestamp"]);
   $this->doctrine->getManager()->persist($navisionSync);
   $this->doctrine->getManager()->flush();
   //------   Critical Section END   ------
   //------   Remove Lock Mutex    ------
   fclose($fp);
}
}
?>
