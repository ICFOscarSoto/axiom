<?php
namespace App\Modules\Email\Proccessors;
use App\Modules\Email\Utils\EmailUtils;
use App\Helpers\HelperMail;
use App\Modules\Email\Helpers\HelperVelilla;
use App\Modules\Email\Helpers\HelperArcos;
use App\Modules\Email\Entity\VelillaReadFilter;
use App\Modules\Globale\Utils\GlobaleTranslatorUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class juanjo_roncero¡ferreteriacampollano_com{

    /** string $rootPath */
     private $rootPath;

     public function __construct(string $rootPath)
     {
         $this->rootPath = $rootPath;
     }
    public function checkMail($subject, $account, $inbox, $output, $doctrine, $kernel){
      $discordchannel_critico="883046233017552956";
      $output->writeln([isset($subject->subject)?('Procesando: '.HelperMail::decode_header(imap_utf8($subject->subject))):('Procesando: Correo sin asunto')]);
      $from=HelperMail::decode_header(imap_utf8($subject->from));
      if(strpos($from,"<")!==FALSE){
        $start=strpos($from,'<')+1;
        $end=strpos($from,'>',$start);
        $from=substr($from,$start,$end-$start);
      }


      switch ($from) {

        case 'informacionvelilla@velillaconfeccion.es':
          $subjectEmail=isset($subject->subject)?HelperMail::decode_header(imap_utf8($subject->subject)):'';
          if(strpos($subjectEmail,"Informe Control de stock Velilla")!==FALSE){
            $emailUtils = new EmailUtils();
            $emailUtils->getmsg($inbox,$subject->msgno);
            $attachments=$emailUtils->attachments;
            $xlsm=null;
            foreach($attachments as $attachment){
              if(strpos($attachment["filename"],".xlsm")!==FALSE){
                $order_attachment=$attachment;
                break;
              }
            }


            if(!$order_attachment){
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel_critico."&msg=".urlencode(":warning:"."SCRIPT ".basename(__FILE__, '.php').": Posible email de Velilla sin adjunto XLSM"));
              break;
            }



            $xlsm=$emailUtils->getAtachment($inbox,$order_attachment["msgno"],$order_attachment["encoding"],$order_attachment["partno"]);
            $tempPath=$kernel->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$account->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$account->getUser()->getId().DIRECTORY_SEPARATOR.'Email'.DIRECTORY_SEPARATOR;

            $output->writeln($tempPath);

            if (!file_exists($tempPath) && !is_dir($tempPath)) {
                mkdir($tempPath, 0775, true);
            }


             $file = fopen($tempPath."archivo.xlsm", "w");
             fwrite($file,$xlsm);


            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($tempPath."archivo.xlsm");

            HelperVelilla::parseStocks($spreadsheet, $doctrine, $output);
            fclose($file);
            unlink($tempPath."archivo.xlsm");

            //SCP.......
          }
        break;

        case 'mailer@arcos.com':
          $subjectEmail=isset($subject->subject)?HelperMail::decode_header(imap_utf8($subject->subject)):'';
          if(strpos($subjectEmail,"Stock")!==FALSE){
            $emailUtils = new EmailUtils();
            $emailUtils->getmsg($inbox,$subject->msgno);
            $attachments=$emailUtils->attachments;
            $csv=null;
            foreach($attachments as $attachment){
              if(strpos($attachment["filename"],".CSV")!==FALSE){
                $order_attachment=$attachment;
                break;
              }
            }

            if(!$order_attachment){
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel_critico."&msg=".urlencode(":warning:"."SCRIPT ".basename(__FILE__, '.php').": Posible email de Arcos sin adjunto CSV"));
              break;
            }

            $csv=$emailUtils->getAtachment($inbox,$order_attachment["msgno"],$order_attachment["encoding"],$order_attachment["partno"]);
            HelperArcos::parseStocks($csv, $doctrine, $output);

          }
        break;


      }
    }

}
?>
