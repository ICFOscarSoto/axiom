<?php
namespace App\Modules\Globale\Helpers;

use App\Modules\Globale\Entity\GlobaleComments;

class HelperComments{
  public function getComments($entity, $entity_id, $user, $doctrine){
      $commentsrepository = $doctrine->getRepository(GlobaleComments::class);
      $comments = $commentsrepository->getComments($entity, $entity_id, $user->getCompany());  
      return $comments;

  }
}
