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
//use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader;

class juanjo_roncero¡ferreteriacampollano_com {

    public function checkMail($subject, $account, $inbox, $output, $doctrine){
      $discordchannel="883046233017552956";
      $output->writeln([isset($subject->subject)?('Procesando: '.HelperMail::decode_header(imap_utf8($subject->subject))):('Procesando: Correo sin asunto')]);
      $from=HelperMail::decode_header(imap_utf8($subject->from));
      if(strpos($from,"<")!==FALSE){
        $start=strpos($from,'<')+1;
        $end=strpos($from,'>',$start);
        $from=substr($from,$start,$end-$start);
      }


      switch ($from) {
        /*
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
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode(":warning:"."SCRIPT ".basename(__FILE__, '.php').": Posible email de Velilla sin adjunto XLSM"));
              break;
            }

            $xlsm=$emailUtils->getAtachment($inbox,$order_attachment["msgno"],$order_attachment["encoding"],$order_attachment["partno"]);


            $reader = new Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($xlsm);
            HelperVelilla::parseStocks($spreadsheet, $doctrine);
          }
        break;
        */
        case 'Joaquin.Martinez@arcos.com':
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
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode(":warning:"."SCRIPT ".basename(__FILE__, '.php').": Posible email de Arcos sin adjunto CSV"));
              break;
            }

            $csv=$emailUtils->getAtachment($inbox,$order_attachment["msgno"],$order_attachment["encoding"],$order_attachment["partno"]);

            //$csv=fopen($item->fileSelect,"r");
            HelperArcos::parseStocks($csv, $doctrine, $output);

        }
        break;


      }
    }

}
?>
