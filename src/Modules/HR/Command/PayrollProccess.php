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
use \App\Helpers\HelperFiles;
use App\Modules\Globale\Helpers\PdfParser\Parser;
use App\Modules\Globale\Helpers\PdfParser\Config;
use App\Service\ContainerParametersHelper;

class PayrollProccess extends ContainerAwareCommand
{

  private $doctrine;
  private $entityManager;
  private $configpaths;

  public function __construct(array $configpaths){
      $this->configpaths=$configpaths;
       parent::__construct();
   }

  protected function configure(){
        $this
            ->setName('payroll:send')
            ->setDescription('Proccess payroll file')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    //Directorios de trabajo
    $ocrDir=$this->configpaths["payroll_preOCR"];
    $tempDir=$this->configpaths["payroll_temp"];

    $sourceFile=$ocrDir.'nominas.pdf';

    //Comprobamos que existan los directorios de trabajo
    if(!file_exists($ocrDir) || !is_dir($ocrDir))
      die();
    if(!file_exists($tempDir) || !is_dir($tempDir))
      die();
    if(!file_exists($sourceFile))
      die();

    //Recorremos todos los archivos en el directorio
    $date=new \DateTime();
    //Obtener el numero de páginas del pdf
    $result=shell_exec("qpdf --show-npages  \"".$ocrDir.$fileinfo->getFilename()."\"");
    //Partir el fichero en archivos de una unica hoja
    echo("CMD: "."gs -o \"".$tempDir.uniqid("file")."_%04d.pdf\" -sDEVICE=pdfwrite \"".$sourceFile."\"");
    $result=shell_exec("gs -o \"".$tempDir.uniqid("file")."_%04d.pdf\" -sDEVICE=pdfwrite \"".$sourceFile."\"");

    //Borrar archivo original
    unlink($sourceFile);


  }


}
?>
