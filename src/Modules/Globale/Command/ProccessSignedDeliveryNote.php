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

class ProccessSignedDeliveryNote extends ContainerAwareCommand
{

  private $doctrine;
  private $entityManager;
  private $configpaths;

  public function __construct(array $configpaths)
   {
      $this->configpaths=$configpaths;
       parent::__construct();
   }



  protected function configure(){
      //$this->pathHelpers = $paths;
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
    $tempDir=$this->configpaths["signedDeliveryNotes_temp"];
    $failsDir=$this->configpaths["signedDeliveryNotes_fail"];
    $destDir=$this->configpaths["signedDeliveryNotes"];

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
          preg_match('/([0-9]{2})\/([0-9]{2})\/([0-9]{2})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE);
          if(count($matches)>0) $documentDate=$matches[0][1]; else $documentDate=FALSE;
          if($documentDate!==FALSE){
            $documentDate=substr($pdf->getText(), $documentDate+strcspn($pdf->getText(),'0123456789',$documentDate), 8);
            $date = \DateTime::createFromFormat('d/m/y', $documentDate);
            if($date===false) $date = new \DateTime();
          }else{
            //No se encuentra la palabra fecha, por defecto usamos la fecha actual
            $date = new \DateTime();
          }
          $documentDate=$date->format('Y-m-d');
          //Obtener las posiciones de los patrones de numero de documento
          preg_match('/([0-9]{2})ALV([0-9]{6})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE);
          if(count($matches)>0) $deliveryNoteNumber=$matches[0][1]; else $deliveryNoteNumber=9223372036854775807;
          preg_match('/([0-9]{2})FC([0-9]{6})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE);
          if(count($matches)>0) $invoiceNumber=$matches[0][1]; else $invoiceNumber=9223372036854775807;
          preg_match('/([0-9]{2})TI([0-9]{6})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE);
          if(count($matches)>0) $ticketNumber=$matches[0][1]; else $ticketNumber=9223372036854775807;
          preg_match('/([0-9]{2})DVR([0-9]{5})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE);
          if(count($matches)>0) $deliveryReturnNoteNumber=$matches[0][1]; else $deliveryReturnNoteNumber=9223372036854775807;
          preg_match('/([0-9]{2})T1([0-9]{6})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE); //ERROR tipico en OCR 1
          if(count($matches)>0) $ticketNumberError1=$matches[0][1]; else $ticketNumberError1=9223372036854775807;
          preg_match('/([0-9]{2})11([0-9]{6})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE); //ERROR tipico en OCR 2
          if(count($matches)>0) $ticketNumberError2=$matches[0][1]; else $ticketNumberError2=9223372036854775807;
          preg_match('/([0-9]{2})711([0-9]{6})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE); //ERROR tipico en OCR 3
          if(count($matches)>0) $ticketNumberError3=$matches[0][1]; else $ticketNumberError3=9223372036854775807;


          //Elegimos el tipo de documento que primero aparezca en el archivo
          $type=array_keys([$deliveryNoteNumber, $invoiceNumber, $ticketNumber, $deliveryReturnNoteNumber, $ticketNumberError1, $ticketNumberError2, $ticketNumberError3], min([$deliveryNoteNumber, $invoiceNumber, $ticketNumber, $deliveryReturnNoteNumber, $ticketNumberError1, $ticketNumberError2, $ticketNumberError3]));
          $position=min($deliveryNoteNumber, $invoiceNumber, $ticketNumber, $deliveryReturnNoteNumber, $ticketNumberError1, $ticketNumberError2, $ticketNumberError3);
          if($position==9223372036854775807){
            //No se puede leer el numero de albaran movemos a fallidos
            rename ($tempDir.$fileinfo->getFilename(), $failsDir.$date->format('Y-m-d-His').$fileinfo->getFilename());
            file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?msg=".urlencode(":bookmark_tabs: No se pudo detectar el número de documento en el fichero digitalizado: ".$date->format('Y-m-d-His').$fileinfo->getFilename().""));
            continue;
          }
          $documentNumber=substr($pdf->getText(), $position, 12);
          //Limpiamos cualquier cosa que no sea letras y numeros
          $documentNumber = preg_replace("/[^a-zA-Z0-9]+/", "", $documentNumber);
          //Creamos el nombre del fichero normalizado
          $filename=$documentDate.' - '.$documentNumber.'.pdf';
          //Crear estructura de carpetas final
          $fileDestDir=$destDir.$date->format('Y').DIRECTORY_SEPARATOR.$date->format('m').DIRECTORY_SEPARATOR.$date->format('d').DIRECTORY_SEPARATOR;
          if(!file_exists($fileDestDir) || !is_dir($fileDestDir))
            mkdir($fileDestDir, 0777, true);
          //Comprobamos si existe ya el fichero en el destino
          if(file_exists($fileDestDir.$filename) && is_file($fileDestDir.$filename)){
            //Cambiamos el nombre del fichero destino
            rename ($fileDestDir.$filename, $fileDestDir.'temp_'.$filename);
            //Adjuntamos el pdf al fichero existente
            $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=\"".$fileDestDir.$filename."\" \"".$fileDestDir.'temp_'.$filename."\" \"".$tempDir.$fileinfo->getFilename()."\"";
            $result = shell_exec($cmd);
            //Borramos los dos ficheros temporales
            unlink($fileDestDir.'temp_'.$filename);
            unlink($tempDir.$fileinfo->getFilename());
          }else{
            //Renombramos y movemos
            rename ($tempDir.$fileinfo->getFilename(), $fileDestDir.$filename);
          }
          $output->writeln($filename);
        }
    }

  }


}
?>
