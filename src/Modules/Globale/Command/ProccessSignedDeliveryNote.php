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


class ProccessSignedDeliveryNote extends ContainerAwareCommand
{
  private $doctrine;
  private $entityManager;
  protected function configure(){
        $this
            ->setName('signeddeliverynote:proccess')
            ->setDescription('Programmed tasks of Axiom')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    //Directorios de trabajo
    $tempDir=__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'ERPSignedDeliveryNotes'.DIRECTORY_SEPARATOR;
    $destDir=__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'ERPSignedDeliveryNotes'.DIRECTORY_SEPARATOR;

    //Comprobamos que existan los directorios de trabajo
    if(!file_exists($tempDir) || !is_dir($tempDir))
      mkdir($tempDir, 0777, true);
    if(!file_exists($destDir) || !is_dir($destDir))
      mkdir($destDir, 0777, true);

    //Configuramos el parseador del pdf
    $config = new Config();
    $config->setHorizontalOffset("\t");
    $parser = new Parser([], $config);
    $date = null;

    //Recorremos todos los archivos en el directorio
    $dir = new \DirectoryIterator($tempDir);
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
          $pdf = $parser->parseFile($tempDir.$fileinfo->getFilename());
          //Obtener la fecha del documento
          $deliveryNoteDate=strpos($pdf->getText(),'FECHA');
          if($deliveryNoteDate!==FALSE){
            //A partir del primer numero despues de la palabra FECHA
            $deliveryNoteDate=substr($pdf->getText(), $deliveryNoteDate+strcspn($pdf->getText(),'0123456789',$deliveryNoteDate), 8);
            $date = \DateTime::createFromFormat('d/m/y', $deliveryNoteDate);
            if($date===false) $date = new \DateTime();
          }else{
            //No se encuentra la palabra fecha, por defecto usamos la fecha actual
            $date = new \DateTime();
          }
          $deliveryNoteDate=$date->format('Y-m-d');
          //Obtener el numero de documento
          $deliveryNoteNumber=strpos($pdf->getText(),$date->format('y').'ALV');
          if($deliveryNoteNumber!==FALSE){
            $deliveryNoteNumber=substr($pdf->getText(), $deliveryNoteNumber, 12);
            $deliveryNoteNumber = preg_replace("/[^a-zA-Z0-9]+/", "", $deliveryNoteNumber);
          }else{
            //No se puede leer el numero de albaran borramos el archivo
            unlink($tempDir.$fileinfo->getFilename());
            continue;
          }
          //Creamos el nombre del fichero normalizado
          $filename=$deliveryNoteDate.' - '.$deliveryNoteNumber.'.pdf';
          //Crear estructura de carpetas final
          $fileDestDir=$destDir.$date->format('Y').DIRECTORY_SEPARATOR.$date->format('m').DIRECTORY_SEPARATOR.$date->format('d').DIRECTORY_SEPARATOR;
          if(!file_exists($fileDestDir) || !is_dir($fileDestDir))
            mkdir($fileDestDir, 0777, true);
          //Renombramos y movemos
          rename ($tempDir.$fileinfo->getFilename(), $fileDestDir.$filename);
          $output->writeln($filename);
        }
    }

  }


}
?>
