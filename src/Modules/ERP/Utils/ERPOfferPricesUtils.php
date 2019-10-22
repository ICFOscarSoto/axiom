<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPShoppingDiscounts;

class ERPOfferPricesUtils
{
  private $module="ERP";
  private $name="OfferPrices";
  public $parentClass="\App\Modules\ERP\Entity\ERPProducts";
  public $parentField="product";


  public function formatListByProduct($product){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $product,
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

  public function getExcludedForm($params){
      return ['product'];
  }

  public function getIncludedForm($params){
    
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
  //  $productRepository=$doctrine->getRepository(ERPProducts::class);
//    $product=$productRepository->findOneBy(["id"=>$params["parent"]]);
      //return [];
    
    return [
    ['shoppingprice', TextType::class, [
      'required' => false,
      'disabled' => true,
      'attr'=> ["readonly"=>true],
      'mapped' => false,
      'data' => $params["parent"]->getShoppingPrice($doctrine)
    ]]
    ];
  }

  public function formatList($user, $product){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $product,
                        "id" => $product,
                        "field" => "product",
                        "parentModule" => "ERP",
                        "parentName" => "OfferPrices"
                      ],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/OfferPrices.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/OfferPricesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/OfferPricesTopButtons.json"),true)
    ];
    return $list;
  }
}
