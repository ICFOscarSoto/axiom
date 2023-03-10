<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\Security\Utils\SecurityUtils;

class ERPProductsUtils
{
  public function getExcludedForm($params){
    return ['product'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $id=$params["id"];
    $productRepository=$doctrine->getRepository(ERPProducts::class);
    $products=$productRepository->findOneBy(["id"=>$id]);
    $permissions=SecurityUtils::getZonePermissions($user, $doctrine);

    if(isset($permissions['field_form_selectcategory']) && $permissions['field_form_selectcategory']['allowaccess']==false) {
      $readonly=true;
    }else $readonly=false;


    if($products==null)
      $array=[
      ['shoppingdiscounts', TextType::class, [
        'required' => false,
        'disabled' => true,
        'attr'=> ["readonly"=>true],
        'mapped' => false,
        'data' => 0
      ]],
      ['selectcategory', ButtonType::class, [
        "attr"=> ['disabled' => $readonly, "class"=>"transform-button"]
      ]]];
    else $array=[
      ['shoppingdiscounts', TextType::class, [
        'required' => false,
        'disabled' => true,
        'attr'=> ["readonly"=>true],
        'mapped' => false,
        'data' => $products->getShoppingDiscount($doctrine, $products->getSupplier())
      ]],
      ['selectcategory', ButtonType::class, [
        "attr"=> ['disabled' => $readonly, "class"=>"transform-button"]
      ]]
      ];

    return $array;
  }

  public function formatList($user){
    $list=[
      'id' => 'listProducts',
      'route' => 'productlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Products.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductsTopButtons.json"),true)
    ];
    return $list;
  }
}
