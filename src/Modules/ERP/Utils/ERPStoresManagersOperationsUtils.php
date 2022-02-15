<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class ERPStoresManagersOperationsUtils
{
  private $module="ERP";
  private $name="StoresManagersOperations";
  public function getExcludedForm($params){
    return [];
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
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }


  public function formatCustomizedOperationsList($id,$start,$end,$store){
    $list=[
      'id' => 'listOperations',
      'route' => 'operationslist',
      'routeParams' => ["id" => $id,
                        "module" => $this->module,
                        "name" => $this->name,
                        "start" => $start,
                        "end" => $end,
                        "store" => $store],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersCustomizedOperations.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersCustomizedOperationsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersCustomizedOperationsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatConsumersReportsList($id,$start,$end,$store){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'consumersreportslist',
      'routeParams' => ["id" => $id,
                        "module" => $this->module,
                        "name" => $this->name,
                        "start" => $start,
                        "end" => $end,
                        "store" => $store],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsReports.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsReportsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsReportsTopButtons.json"),true)
    ];
    return $list;
  }


}
