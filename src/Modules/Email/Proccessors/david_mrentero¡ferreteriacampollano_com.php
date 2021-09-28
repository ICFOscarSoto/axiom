<?php
namespace App\Modules\Email\Proccessors;
use App\Modules\Email\Utils\EmailUtils;
use App\Helpers\HelperMail;
use App\Modules\Email\Helpers\HelperMercateo;
use App\Modules\Globale\Utils\GlobaleTranslatorUtils;

class david_mrentero¡ferreteriacampollano_com {

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
        case 'sistemas@ferreteriacampollano.com':
          $subjectEmail=isset($subject->subject)?HelperMail::decode_header(imap_utf8($subject->subject)):'';
          $emailUtils = new EmailUtils();
         //SAI POWERMASTER
          if(strpos($subjectEmail,"PowerMaster Agent")!==FALSE){
      			  $emailUtils->getmsg($inbox,$subject->msgno);
              $content=($emailUtils->htmlmsg!=null)?(preg_match('!!u', $emailUtils->htmlmsg)?$emailUtils->htmlmsg:utf8_encode($emailUtils->htmlmsg)):$emailUtils->plainmsg;
              $h2t=strip_tags($content);
              $startPos=strpos($h2t,"Description:")+13;
              $endPos=strpos($h2t,".",$startPos);
              $h2t=substr($h2t,$startPos,$endPos-$startPos+1);
              $trans = new GlobaleTranslatorUtils();
              $result = $trans->translate('en', 'es', $h2t);
              $output->writeln($result);
              //Select icon
              $icon="";
              if(strpos($subjectEmail,"power failure")!==FALSE) $icon=":warning: ";
              if(strpos($subjectEmail,"power has restored")!==FALSE) $icon=":white_check_mark: ";
              //Send notification
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode($icon."SAI 1: ".$result));
          }
           //LSI Megaraid Navision
           if(strpos($subjectEmail,"Event occured on")!==FALSE){
       			  $emailUtils->getmsg($inbox,$subject->msgno);
               $content=($emailUtils->htmlmsg!=null)?(preg_match('!!u', $emailUtils->htmlmsg)?$emailUtils->htmlmsg:utf8_encode($emailUtils->htmlmsg)):$emailUtils->plainmsg;
               $h2t=strip_tags($content);
               $startPos=strpos($h2t,"Controller ID:")+18;
               $endPos=strpos($h2t,"\n",$startPos);
               $h2t=substr($h2t,$startPos,$endPos-$startPos+1);
               $trans = new GlobaleTranslatorUtils();
               $result = $trans->translate('en', 'es', $h2t);

               //Select icon
               $icon="";
               if(strpos($subjectEmail,"Critical")!==FALSE) $icon=":warning: ";
               if(strpos($subjectEmail,"Fatal")!==FALSE) $icon=":sos: ";
               //Send notification
               file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode($icon."RAID NAVISION: ".$result));
           }
        break;


      }
    }

}
?>
