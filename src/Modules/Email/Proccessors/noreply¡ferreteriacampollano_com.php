<?php
namespace App\Modules\Email\Proccessors;
use App\Modules\Email\Utils\EmailUtils;
use App\Helpers\HelperMail;
use App\Modules\Email\Helpers\HelperMercateo;
use App\Modules\Globale\Utils\GlobaleTranslatorUtils;

class noreply¡ferreteriacampollano_com {

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
        case 'operations@mercateo.es':
        case 'pedidosweb@ferreteriacampollano.com':
          $subjectEmail=isset($subject->subject)?HelperMail::decode_header(imap_utf8($subject->subject)):'';
          if(strpos($subjectEmail,"Pedido de Mercateo")!==FALSE){
            $emailUtils = new EmailUtils();
            $emailUtils->getmsg($inbox,$subject->msgno);
            $attachments=$emailUtils->attachments;
            $order_xml=null;
            foreach($attachments as $attachment){
              if(strpos($attachment["filename"],".xml")!==FALSE){
                $order_attachment=$attachment;
                break;
              }
            }
            if(!$order_attachment){
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode(":warning:"."SCRIPT ".basename(__FILE__, '.php').": Posible pedido Mercateo sin adjunto XML Opentrans"));
              break;
            }
            $order_xml=$emailUtils->getAtachment($inbox,$order_attachment["msgno"],$order_attachment["encoding"],$order_attachment["partno"]);
            $order_xml = new \SimpleXMLElement($order_xml);
            $json=HelperMercateo::convertToOrder($order_xml, $doctrine);
            $postdata = http_build_query(['json' => json_encode($json)]);
            $opts = ['http' =>['method'  => 'POST', 'header'  => 'Content-Type: application/x-www-form-urlencoded', 'content' => $postdata]];
            $context  = stream_context_create($opts);
            $result = file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createFrascab.php', false, $context);
            $result = json_decode($result,true);
            if(json_last_error() !== JSON_ERROR_NONE){
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode(":warning:"."SCRIPT ".basename(__FILE__, '.php').": Error valor retornado al crear pedido Mercateo en Navision Frascab"));
            }else{
              if($result["result"]!=1){
                file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode(":sos:"."SCRIPT ".basename(__FILE__, '.php').": Ocurrieron errores y no se pudo crear pedido Mercateo en Navision Frascab"));
              }
            }
          }
        break;

      }
    }

}
?>
