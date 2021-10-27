<?php
namespace App\Modules\Navision\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\ERP\Entity\ERPInputs;
use App\Modules\ERP\Entity\ERPPurchasesOrdersLines;
use App\Modules\ERP\Entity\ERPPurchasesBudgets;
use App\Modules\ERP\Entity\ERPPurchasesBudgetsLines;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Navision\Entity\NavisionSync;

use \App\Helpers\HelperFiles;


class NavisionGetInputs extends ContainerAwareCommand
{
  private $doctrine;
  private $company;
  private $entityManager;
  private $url="http://192.168.1.250:9000/";

  protected function configure(){
        $this
            ->setName('navision:getinputs')
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
      case 'purchasesorders': $this->importInputs($input, $output);
      break;
      case 'all':
        $this->importInputs($input, $output);
      break;
      default:
        $output->writeln('Opcion no válida');
      break;
    }

  }

    public function importInputs(InputInterface $input, OutputInterface $output){
      //------   Create Lock Mutex    ------
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-navisionGetInputs.lock', 'c');
      } else {
          $fp = fopen('/tmp/axiom-navisionGetPurchasesOrders-navisionGetInputs.lock', 'c');
      }
      if (!flock($fp, LOCK_EX | LOCK_NB)) {
        $output->writeln('* Fallo al iniciar la sincronizacion de pedidos compras: El proceso ya esta en ejecución.');
        exit;
      }

      $repositoryInputs=$this->doctrine->getRepository(ERPInputs::class);
      $repositoryUsers=$this->doctrine->getRepository(GlobaleUsers::class);
      $inputs=$repositoryInputs->findBy(["navinput"=>false, "active"=>1, "deleted"=>0]);
      foreach($inputs as $input){

          $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getPurchaseDeliveryExists.php?code='.$input->getCode().'&supplier='.$input->getSupplier()->getCode());
          $object=json_decode($json, true);
          if(json_last_error() !== JSON_ERROR_NONE){
            continue;
          }else{
            if($object["result"]!=1){
              continue;
            }
          $user=$repositoryUsers->findOneBy(["email"=>$object["data"]["author"]]);
          if(!$user) continue;
          $output->writeln('  - '.$input->getCode().' -> '.$object["data"]["code"]);
          $input->setNavinput(true);
          $input->setNavauthor($user);
          $input->setOurcode($object["data"]["code"]);
          $this->doctrine->getManager()->persist($input);
          $this->doctrine->getManager()->flush();
          }

      }


      //------   Critical Section END   ------
      //------   Remove Lock Mutex    ------
      fclose($fp);
    }

}
?>
