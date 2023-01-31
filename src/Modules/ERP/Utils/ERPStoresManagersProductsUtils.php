<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPProducts;
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
    $manager_id=(isset($params["manager_id"])?$params["manager_id"]:null);
    $user=$params["user"];
    $product=null;
    $productvariant=$params["productvariant"];

    if ($productvariant!=null){
      $product=$productvariant->getProduct();
      $productsvariantsRepository=$doctrine->getRepository(ERPProductsVariants::class);
      $choices = $productsvariantsRepository->getVariants($product, $user);
      if (count($choices)==1)
        $productvariant = $choices[0];
    }

    return [
    ['product', TextType::class, [
        'required' => true,
        'disabled' => false,
        'attr'=> ["readonly"=>false, "data_id"=>($product?$product->getId():''), 'value' => ($product?'('.$product->getCode().') '.$product->getName():'')],
        'mapped' => false

    ]],
    ['productvariant', ChoiceType::class, [
      'required' => true,
      'disabled' => false,
      'mapped' => false,
      'attr' => ['class' => 'select2', 'readonly' => true, 'ajax'=>true],
      'choices' => $productvariant?$productsvariantsRepository->getVariants($product, $user):null,
      'placeholder' => 'Seleccionar',
      'choice_label' => function($obj, $key, $index) {
          return ($obj?($obj->getVariant()?$obj->getVariant()->getName():''):'');
      },
      'choice_value' => 'id',
      'data' => $productvariant
    ]],
    ['manager_id', HiddenType::class, [
        'required' => true,
        'mapped' => false,
        'data' => $manager_id
    ]]
    ];
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
