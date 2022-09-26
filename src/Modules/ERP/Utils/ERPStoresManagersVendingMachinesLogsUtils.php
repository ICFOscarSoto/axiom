<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class ERPStoresManagersVendingMachinesLogsUtils
{
  private $module="ERP";
  private $name="StoresManagersVendingMachinesLogs";
  public function getExcludedForm($params){
    return ['vendingmachine'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    return [];
  }

  public function formatList($user, $id){
    $list=[
      'id' => $this->name,
      'route' => 'StoresManagersVendingMachineLogslist',
      'routeParams' => ["id" => $id],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }
}
