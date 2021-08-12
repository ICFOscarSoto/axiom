<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class ERPStoresManagersOperationsLinesUtils
{
  private $module="ERP";
  private $name="StoresManagersOperationsLines";
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

  public function formatProductsReportsList($id,$start,$end,$store){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'productsreportslist',
      'routeParams' => ["id" => $id,
                        "module" => $this->module,
                        "name" => $this->name,
                        "start" => $start,
                        "end" => $end,
                        "store" => $store],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsReports.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsReportsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsReportsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatConsumersReportsDetailedList($consumerid,$start,$end,$store){
    $list=[
      'id' => 'list2'.$this->name,
      'route' => 'consumersReportsDetailedList',
      'routeParams' => ["module" => $this->module,
                        "name" => "2".$this->name,
                        "consumerid" => $consumerid,
                        "start" => $start,
                        "end" => $end,
                        "store" => $store],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsDetailedReports.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsDetailedReportsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsDetailedReportsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatProductsReportsDetailedList($productid,$start,$end,$store){
    $list=[
      'id' => 'list3'.$this->name,
      'route' => 'productsReportsDetailedList',
      'routeParams' => ["module" => $this->module,
                        "name" => "3".$this->name,
                        "productid" => $productid,
                        "start" => $start,
                        "end" => $end,
                        "store" => $store],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsDetailedReports.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsDetailedReportsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsDetailedReportsTopButtons.json"),true)
    ];
    return $list;
  }


}
