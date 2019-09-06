<?php
namespace App\Modules\Globale\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRAutoCloseClocks;
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\HR\Entity\HRWorkCenters;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use App\Modules\Globale\Entity\GlobaleHistories;

class CalculateDiskUsage extends ContainerAwareCommand
{
  private $doctrine;
  private $entityManager;
  protected function configure(){
        $this
            ->setName('globale:calculatedisk')
            ->setDescription('Programmed tasks of Axiom')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();

    //Execute different tasks
    $output->writeln([__DIR__]);
    $this->GlobaleCalculateDiskUsage($output);
  }

  function GlobaleCalculateDiskUsage($output){
    $companiesRepository = $this->doctrine->getRepository(GlobaleCompanies::class);
    $diskusagesRepository = $this->doctrine->getRepository(GlobaleDiskUsages::class);
    $companies=$companiesRepository->findAll();
    foreach($companies as $key=>$item){
        $source = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$item->getId();
        $size=$this->GetDirectorySize($source);
        $size=round($size/1048576,4);  //Convert to MB
        $output->writeln(['   - '.$item->getName()." -> ".$size. " MB"]);
        $diskusage=$diskusagesRepository->findOneBy(["company"=>$item]);
        $parts = array_diff(scandir($source), array('..', '.', 'temp'));
        $distribution=[];
        foreach($parts as $part){
          $size_sub=$this->GetDirectorySize($source.DIRECTORY_SEPARATOR.$part);
          $size_sub=round($size_sub/1048576,4);
          $distribution[$part]=$size_sub;
        }

        if($diskusage!=null){
          $diskusage->setDiskusage($size);
          $diskusage->setDistribution(json_encode($distribution));
          $diskusage->setDateupd(new \DateTime());
        }else{
          $diskusage=new GlobaleDiskUsages();
          $diskusage->setCompany($item);
          $diskusage->setDiskspace(50);
          $diskusage->setDiskusage($size);
          $diskusage->setDistribution(json_encode($distribution));
          $diskusage->setActive(1);
          $diskusage->setDeleted(0);
          $diskusage->setDateadd(new \DateTime());
          $diskusage->setDateupd(new \DateTime());
        }
        $this->entityManager->persist($diskusage);
        $this->entityManager->flush();
    }
  }

  function GetDirectorySize($path){
      $bytestotal = 0;
      $path = realpath($path);
      if($path!==false && $path!='' && file_exists($path)){
          foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object){
              $bytestotal += $object->getSize();
          }
      }
      return $bytestotal;
  }
}
?>
