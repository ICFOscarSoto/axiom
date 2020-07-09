<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;

class ERPCustomerCommentLinesUtils
{
  private $module="ERP";
  private $name="CustomerCommentLines";
  public $parentClass="\App\Modules\ERP\Entity\ERPCustomers";
  public $parentField="customer";


  public function formatListByCustomer($customer){
    $list=[
      'id' => 'listCustomerCommentLinesDataOrder',
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $customer,
                        "field" => "customer",
                        "parentModule" => "ERP",
                        "parentName" => "Customers"
                      ],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListByCustomerType($customer,$type){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'customercommentlineslist',
      'routeParams' => ["customerid" => $customer, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListByCustomerTypeOrdersData($type,$parent){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'customercommentlinesordersdatalist',
      'routeParams' => ["customerid" => $parent, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerCommentLinesOrdersData.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerCommentLinesOrdersDataFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerCommentLinesOrdersDataTopButtons.json"),true)
    ];
    return $list;
  }


  public function getExcludedForm($params){
    return ["customer","type"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $id=$params["id"];

    return [];
  }
}
