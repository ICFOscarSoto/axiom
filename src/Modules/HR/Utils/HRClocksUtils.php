<?php
namespace App\Modules\HR\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ListUtils;

class HRClocksUtils
{
  public function formatList($user){
    $list=[
      'id' => 'listClocks',
      'route' => 'clockslist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 4,
      'orderDirection' => 'DESC',
      'tagColumn' => 4,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListbyWorker($worker){
    $list=[
      'id' => 'listClocks',
      'route' => 'clockslistworker',
      'routeParams' => ["id" => $worker],
      'orderColumn' => 4,
      'orderDirection' => 'DESC',
      'tagColumn' => 4,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksTopButtons.json"),true)
    ];
    return $list;
  }


}
