<?php
namespace App\Modules\Globale\Helpers;

use App\Modules\Globale\Helpers\HelperComments;
use App\Modules\Globale\Helpers\HelperDatetime;
use App\Modules\Globale\Helpers\HelperHistory;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Globale\Entity\GlobaleComments;
use App\Modules\Globale\Helpers\Html2Text\Html2Text;


class HelperHistory{
  public function orderHistoryElements($a, $b) {
      if($a['dateadd']>=$b['dateadd'])
        return -1;
      else return 1;
  }

  public function createHistory($entity, $entity_id, $user, $doctrine, $addComments = true){

      $comments = HelperComments::getComments($entity, $entity_id, $user, $doctrine);
      $emails = HelperComments::getEmails($entity, $entity_id, $user, $doctrine);
      $calls = HelperComments::getCalls($entity, $entity_id, $user, $doctrine);
      $histories = HelperHistory::getHistories($entity, $entity_id, $user, $doctrine);

      //Adaptamos las fechas a formatos de lectura humana
      foreach($comments as $key=>$comment){
        $comments[$key]["formateddate"]=date_create_from_format('Y-m-d H:i:s', $comment["dateadd"])->format('d/m/Y');
        $comments[$key]["formatedtime"]=date_create_from_format('Y-m-d H:i:s', $comment["dateadd"])->format('H:i');
        $comments[$key]["agodate"]=HelperDatetime::getTimeAgo(date_create_from_format('Y-m-d H:i:s', $comment["dateadd"])->getTimestamp());
      }
      foreach($emails as $key=>$mail){
        $plainContent = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '',$mail["content"]);
        @ $plainContent=Html2Text::convert($plainContent);
        unset($emails[$key]["content"]);
        $emails[$key]["shortcontent"]=substr($plainContent,0, 250);
        $emails[$key]["formateddate"]=date_create_from_format('Y-m-d H:i:s', $mail["dateadd"])->format('d/m/Y');
        $emails[$key]["formatedtime"]=date_create_from_format('Y-m-d H:i:s', $mail["dateadd"])->format('H:i');
        $emails[$key]["agodate"]=HelperDatetime::getTimeAgo(date_create_from_format('Y-m-d H:i:s', $mail["dateadd"])->getTimestamp());
      }
      foreach($calls as $key=>$call){
        $calls[$key]["formateddate"]=date_create_from_format('Y-m-d H:i:s', $call["dateadd"])->format('d/m/Y');
        $calls[$key]["formatedtime"]=date_create_from_format('Y-m-d H:i:s', $call["dateadd"])->format('H:i');
        $calls[$key]["agodate"]=HelperDatetime::getTimeAgo(date_create_from_format('Y-m-d H:i:s', $call["dateadd"])->getTimestamp());
      }
      foreach($histories as $key=>$history){
        $histories[$key]["formateddate"]=date_create_from_format('Y-m-d H:i:s', $history["dateadd"])->format('d/m/Y');
        $histories[$key]["formatedtime"]=date_create_from_format('Y-m-d H:i:s', $history["dateadd"])->format('H:i');
        $histories[$key]["agodate"]=HelperDatetime::getTimeAgo(date_create_from_format('Y-m-d H:i:s', $history["dateadd"])->getTimestamp());
        $histories[$key]["changes"]=json_decode($history["changes"], true);
        $histories[$key]["changes2"]=json_decode($history["changes"], true);
      }
      $items=array_merge($comments, $emails, $calls, $histories);
      usort($items, array('\App\Modules\Globale\Helpers\HelperHistory','orderHistoryElements'));

      $result['entity']=$entity;
      $result['entity_id']=$entity_id;
      $result['userid']=$user->getId();
      $result['username']=$user->getName();
      $result['userlastname']=$user->getLastname();
      $result['elements']=$items;
      $result['budgets']['comments']=count($comments);
      $result['budgets']['calls']=count($calls);
      $result['budgets']['emails']=count($emails);
      return $result;
  }

  public function getHistories($entity, $entity_id, $user, $doctrine){
    $historiesrepository = $doctrine->getRepository(GlobaleHistories::class);
    $histories = $historiesrepository->getHistories($entity, $entity_id, $user->getCompany());
    return $histories;
  }
}
