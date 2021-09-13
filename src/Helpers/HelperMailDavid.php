<?php
namespace App\Helpers;
use App\Modules\Email\Utils\EmailUtils;
use App\Modules\Globale\Utils\GlobaleTranslatorUtils;

class HelperMailDavid {

    public function checkMail($subject, $account, $inbox, $output){
      $discordchannel="883046233017552956";
      $output->writeln([isset($subject->subject)?('Procesando: '.HelperMail::decode_header(imap_utf8($subject->subject))):('Procesando: Correo sin asunto')]);
      $from=HelperMail::decode_header(imap_utf8($subject->from));
      $start=strpos($from,'<')+1;
      $end=strpos($from,'>',$start);
      $from=substr($from,$start,$end-$start);

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
              file_get_contents("https://icfbot.ferreteriacampollano.com/message.php?channel=".$discordchannel."&msg=".urlencode(":warning: ".$result));
          }
          break;

      }
    }

}
?>
