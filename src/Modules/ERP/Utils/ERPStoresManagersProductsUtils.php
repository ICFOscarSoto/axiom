<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPProductsVariants;

class ERPStoresManagersProductsUtils
{
  private $module="ERP";
  private $name="StoresManagersProducts";
  public $parentClass="\App\Modules\ERP\Entity\ERPStoresManagers";
  public $parentField="manager";
  public function getExcludedForm($params){
    return ['manager','productvariant'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $productvariant=$params["productvariant"];
    $product=$params["product"];
    $productsvariantsRepository=$doctrine->getRepository(ERPProductsVariants::class);
    $choices = $productsvariantsRepository->getVariants($product, $user);
    return [
    ['productvariant', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $product?$productsvariantsRepository->findBy(["product"=>$product]):null,
      'placeholder' => 'Selecciona variante',
      'choice_label' => function($obj, $key, $index) {
          return ($obj?($obj->getVariant()?$obj->getVariant()->getName():''):'');
      },
      'choice_value' => 'id',
      'data' => $productvariant
    ]]];
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $parent,
                        "id" => $parent,
                        "field" => "manager",
                        "parentModule" => "ERP",
                        "parentName" => "StoresManagers"
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
