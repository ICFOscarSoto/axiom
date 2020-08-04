<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPSuppliers;


class ERPEAN13Utils
{
  private $module="ERP";
    private $name="EAN13";

  public function formatListByProduct($product){
    $list=[
      'id' => 'listEAN13',
      'route' => 'EAN13list',
      'routeParams' => ["id" => $product],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/EAN13.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/EAN13FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/EAN13TopButtons.json"),true)
    ];
    return $list;
  }


  public function getExcludedForm($params){
    return ['product','supplier', 'productvariant'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $product=$params["product"];
    $supplier=$params["supplier"];
    $productvariant=$params["productvariant"];
    $productsvariantsRepository=$doctrine->getRepository(ERPProductsVariants::class);
    $suppliersRepository=$doctrine->getRepository(ERPSuppliers::class);
    return [
    ['productvariant', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $product?$productsvariantsRepository->findBy(["product"=>$product->getId()]):null,
      'placeholder' => 'Select a product',
      'choice_label' => function($obj, $key, $index) {

          return $obj->getVariantvalue()->getName();
      },
      'choice_value' => 'id',
      'data' => $productvariant
    ]],
    ['supplier', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $product?$suppliersRepository->findBy(["company"=>$user->getCompany()]):null,
      'placeholder' => 'Select a supplier',
      'choice_label' => function($obj, $key, $index) {
          return $obj->getName();
      },
      'choice_value' => 'id',
      'data' => $supplier
    ]]
  ];
  }
}
