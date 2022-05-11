<?php
namespace App\Modules\Globale\Helpers;

use App\Modules\Globale\Helpers\HelperComments;
use App\Modules\Globale\Helpers\HelperDatetime;
use App\Modules\Globale\Entity\GlobaleComments;

class HelperHistory{
  public function createHistory($entity, $entity_id, $user, $doctrine, $addComments = true){

      $comments = HelperComments::getComments($entity, $entity_id, $user, $doctrine);
      //Adaptamos las fechas a formatos de lectura humana
      foreach($comments as $key=>$comment){
        $comments[$key]["formateddate"]=date_create_from_format('Y-m-d H:i:s', $comment["dateadd"])->format('d/m/Y');
        $comments[$key]["formatedtime"]=date_create_from_format('Y-m-d H:i:s', $comment["dateadd"])->format('H:i');
        $comments[$key]["agodate"]=HelperDatetime::getTimeAgo(date_create_from_format('Y-m-d H:i:s', $comment["dateadd"])->getTimestamp());
      }  
      $result['entity']=$entity;
      $result['entity_id']=$entity_id;
      $result['userid']=$user->getId();
      $result['username']=$user->getName();
      $result['userlastname']=$user->getLastname();
      $result['elements']=$comments;
      return $result;
  }
}
