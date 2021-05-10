<?php
namespace App\Modules\HR\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class HRMeetingsUtils
{
  private $module="HR";
  private $name="Meetings";
  public function getExcludedForm($params){
    return [];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $obj=$params["obj"];
    $summoneds=$params["summoneds"];
    $status=$params["status"];
    return [
      ['meeting-status',HiddenType::class, ['mapped'=>false, 'data' => $status]]
    ];
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'meetinglist',
      /*'routeParams' => ["module" => $this->module,
                        "name" => $this->name],*/
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }
  public function formatSummonedsList($id){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'meetingsummonedslist',
      'routeParams' => ["id" => $id],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/MeetingsSummoneds.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/MeetingsSummonedsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/MeetingsSummonedsTopButtons.json"),true)
    ];
    return $list;
  }
}
