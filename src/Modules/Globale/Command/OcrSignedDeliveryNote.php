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

class OcrSignedDeliveryNote extends ContainerAwareCommand
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
            ->setName('signeddeliverynote:ocr')
            ->setDescription('OCR Signed delivery notes')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    //Directorios de trabajo
    $ocrDir=$this->configpaths["signedDeliveryNotes_preOCR"];
    $tempDir=$this->configpaths["signedDeliveryNotes_temp"];

    //Comprobamos que existan los directorios de trabajo
    if(!file_exists($ocrDir) || !is_dir($ocrDir))
      die();
    if(!file_exists($tempDir) || !is_dir($tempDir))
      die();

    //Recorremos todos los archivos en el directorio
    $date=new \DateTime();
    $dir = new \DirectoryIterator($ocrDir);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
          //Obtener el numero de páginas del pfg
          $result=shell_exec("qpdf --show-npages  \"".$ocrDir.$fileinfo->getFilename()."\"");
          $pages=intval($result);
          echo("Páginas: ".$pages);
          if($pages>1){
            //Partir el fichero en archivos de una unica hoja
            echo("CMD: "."gs -o \"".$ocrDir.basename($fileinfo->getFilename(), '.pdf')."_%04d.pdf\" -sDEVICE=\"".$ocrDir.$fileinfo->getFilename()."\"");
            $result=shell_exec("gs -o \"".$ocrDir.basename($fileinfo->getFilename(), '.pdf')."_%04d.pdf\" -sDEVICE=\"".$ocrDir.$fileinfo->getFilename()."\"");
            unlink($ocrDir.$fileinfo->getFilename());
          }elseif($pages==1){
            //Pasar OCR y convertir en PDF buscables
            $result=shell_exec("ocrmypdf -l spa -r --force-ocr --rotate-pages-threshold 5 ".$ocrDir.$fileinfo->getFilename()." ".$tempDir.$fileinfo->getFilename());
            echo($result);
            unlink($ocrDir.$fileinfo->getFilename());
          }
          $output->writeln($fileinfo->getFilename());
        }
    }

  }


}
?>
