<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class ERPSupplierCommentLinesUtils
{
  private $module="ERP";
  private $name="SupplierCommentLines";

  public function formatListBySupplier($supplier){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $supplier,
                        "field" => "supplier",
                        "parentModule" => "ERP",
                        "parentName" => "Suppliers"
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


  public function formatListBySupplierType($supplier,$type){
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'suppliercommentlineslist',
      'routeParams' => ["supplierid" => $supplier, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListBySupplierTypeOrdersData($type,$parent){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'suppliercommentlinesordersdatalist',
      'routeParams' => ["supplierid" => $parent, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersData.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersDataFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersDataTopButtons.json"),true)
    ];
    return $list;
  }


  public function formatListBySupplierTypeOrdersDataRappel($type,$parent){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'suppliercommentlinesordersdatarappellist',
      'routeParams' => ["supplierid" => $parent, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersDataRappel.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersDataRappelFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersDataRappelTopButtons.json"),true)
    ];
    return $list;
  }


  public function formatListBySupplierTypeIncidents($type,$parent){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'suppliercommentlinesincidentslist',
      'routeParams' => ["supplierid" => $parent, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesIncidents.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesIncidentsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesIncidentsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListBySupplierTypeShippings($type,$parent){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'suppliercommentlinesshippingslist',
      'routeParams' => ["supplierid" => $parent, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesShippings.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesShippingsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesShippingsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListBySupplierTypePayments($type,$parent){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'suppliercommentlinespaymentslist',
      'routeParams' => ["supplierid" => $parent, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesPayments.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesPaymentsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesPaymentsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListBySupplierTypeSpecials($type,$parent){;
    $list=[
      'id' => 'list'.$this->name.$type,
      'route' => 'suppliercommentlinesspecialslist',
      'routeParams' => ["supplierid" => $parent, "type"=>$type],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesSpecials.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesSpecialsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesSpecialsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return ["supplier","type"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $id=$params["id"];

    return [];
  }
}
