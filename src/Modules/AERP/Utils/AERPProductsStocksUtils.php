<?php
namespace App\Modules\AERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class AERPProductsStocksUtils
{
  private $module="AERP";
  private $name="ProductsStocks";
  public $parentClass="\App\Modules\AERP\Entity\AERPProducts";
  public $parentField="product";

  public function getExcludedForm($params){
  return ['product','location'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $parent=$params["parent"];
    $repository=$doctrine->getRepository('\App\Modules\AERP\Entity\AERPWarehouseLocations');
    return [
      ['location', ChoiceType::class, [
        'required' => true,
        'disabled' => false,
        'attr' => ['class' => 'select2', 'readonly' => true],
        'choices' => $repository->findNotUsedByProduct($parent->getId(), $user),
        'placeholder' => 'Select aerpwarehouselocations',
        'choice_label' => function($obj, $key, $index) {
          return $obj->getName();
        },
        'choice_attr' => function($obj, $key, $index) {
          return ['class' => $obj->getId()];
        },
        'choice_value' => 'id'
      ]]
    ];
  }

  public function formatList($user, $parent){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $parent,
                        "id" => $parent,
                        "field" => "product",
                        "parentModule" => "AERP",
                        "parentName" => "Products"
                      ],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }
}
