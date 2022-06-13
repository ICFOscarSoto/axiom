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
    $dir = new \DirectoryIterator($ocrDir);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
          $result=shell_exec("ocrmypdf -l spa -r --force-ocr --rotate-pages-threshold 5 ".$ocrDir.$fileinfo->getFilename()." ".$tempDir.$fileinfo->getFilename());
          echo($result);
          unlink($ocrDir.$fileinfo->getFilename());
          $output->writeln($fileinfo->getFilename());
        }
    }

  }


}
?>
