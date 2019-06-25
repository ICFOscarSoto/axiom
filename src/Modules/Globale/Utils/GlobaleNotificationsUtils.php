<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\GlobaleEmailAccounts;

class GlobaleNotificationsUtils
{
  public function formatList($user){
    $list=[
      'id' => 'listNotifications',
      'route' => 'notificationslist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Notifications.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/NotificationsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/NotificationsTopButtons.json"),true)
    ];
    return $list;
  }
}
