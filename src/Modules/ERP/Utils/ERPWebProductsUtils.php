<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPProducts;

class ERPWebProductsUtils
{

  private $module="ERP";
  private $name="WebProducts";
  public $parentClass="\App\Modules\ERP\Entity\ERPWebProducts";
  public $parentField="product";


  public function getExcludedForm($params){
    return ["product"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    return [];
  }


  public function formatList($user){
    $list=[
      'id' => 'listWebProducts',
      'route' => 'webproductlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProducts.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProductsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProductsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListByProduct($user, $product){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $product,
                        "id" => $product,
                        "field" => "product",
                        "parentModule" => "ERP",
                        "parentName" => "Products"
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
}
