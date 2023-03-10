<?php
namespace App\Modules\Navision\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class NavisionTransfersUtils
{
  private $module="Navision";
  private $name="Transfers";
  public function getExcludedForm($params){
    return ['productvariant'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    return [];
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name],
      'orderColumn' => 3,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListbyUser($userId){
    $list=[
      'id' => 'listTransfersByUser',
      'route' => 'transfersByUserList',
      'routeParams' => ["userId" => $userId],
      'orderColumn' => 3,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/TransfersManagers.json"),true),
      'fieldButtons' => [],
      'topButtons' => [],
      'topButtonReload' => false
    ];
    return $list;
  }
}
