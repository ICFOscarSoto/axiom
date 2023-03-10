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
use App\Modules\ERP\Entity\ERPOfferPrices;

class ERPOfferPricesUtils
{
  private $module="ERP";
  private $name="OfferPrices";
  public $parentClass="\App\Modules\ERP\Entity\ERPProducts";
  public $parentField="product";
  public $parentClassCustomerOfferPrices="\App\Modules\ERP\Entity\ERPCustomers";
  public $parentFieldCustomerOfferPrices="customer";


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

  public function formatListByCustomer($user,$customer){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "json" => "CustomerOfferPrices",
                        "name" => $this->name,
                        "parent" => $customer,
                        "id" => $customer,
                        "field" => "customer",
                        "parentModule" => "ERP",
                        "parentName" => "Customers"
                      ],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerOfferPrices.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerOfferPricesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerOfferPricesTopButtons.json"),true)
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
    $offerpricesRepository=$doctrine->getRepository(ERPOfferPrices::class);
    $offerprice=$offerpricesRepository->findOneBy(["id"=>$id]);

    if($offerprice!=NULL)
    {
      $product=$offerprice->getProduct();
      return [
      ['shoppingprice', TextType::class, [
        'required' => false,
        'disabled' => true,
        'attr'=> ["readonly"=>true],
        'mapped' => false,
        'data' => round($product->getShoppingPrice($doctrine),2)
      ]]
      ];

    }
    else
    {
      return [
      ['shoppingprice', TextType::class, [
        'required' => false,
        'disabled' => true,
        'attr'=> ["readonly"=>true],
        'mapped' => false,
        'data' => round($params["parent"]->getShoppingPrice($doctrine),2)
      ]]
      ];
    }
  }

  public function getExcludedFormCustomerOfferPrices($params){
      return ['customer'];
  }

  public function getIncludedFormCustomerOfferPrices($params){

    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $offerpricesRepository=$doctrine->getRepository(ERPOfferPrices::class);
    $offerprice=$offerpricesRepository->findOneBy(["id"=>$id]);

    if($offerprice!=NULL)
    {
      $product=$offerprice->getProduct();
      return [
      ['shoppingprice', TextType::class, [
        'required' => false,
        'disabled' => true,
        'attr'=> ["readonly"=>true],
        'mapped' => false,
        'data' => round($product->getShoppingPrice($doctrine),2)
      ]]
      ];

    }
    else
    {
      return [
      ['shoppingprice', TextType::class, [
        'required' => false,
        'disabled' => true,
        'attr'=> ["readonly"=>true],
        'mapped' => false,
        'data' => round($params["parent"]->getShoppingPrice($doctrine),2)
      ]]
      ];
    }
  }
/*
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
  }*/
}
