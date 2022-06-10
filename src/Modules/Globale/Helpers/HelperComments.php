<?php
namespace App\Modules\Globale\Helpers;

use App\Modules\Globale\Entity\GlobaleComments;
use App\Modules\Globale\Entity\GlobaleCommentsEmails;
use App\Modules\Globale\Entity\GlobaleCommentsCalls;

class HelperComments{
  public function getComments($entity, $entity_id, $user, $doctrine){
      $commentsrepository = $doctrine->getRepository(GlobaleComments::class);
      $comments = $commentsrepository->getComments($entity, $entity_id, $user->getCompany());
      return $comments;
  }

  public function getEmails($entity, $entity_id, $user, $doctrine){
      $mailsrepository = $doctrine->getRepository(GlobaleCommentsEmails::class);
      $mails = $mailsrepository->getEmails($entity, $entity_id, $user->getCompany());
      return $mails;
  }

  public function getCalls($entity, $entity_id, $user, $doctrine){
      $callsrepository = $doctrine->getRepository(GlobaleCommentsCalls::class);
      $calls = $callsrepository->getCalls($entity, $entity_id, $user->getCompany());
      return $calls;
  }

}
