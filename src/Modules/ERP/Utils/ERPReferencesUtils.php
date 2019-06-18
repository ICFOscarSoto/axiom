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

class ERPReferencesUtils
{



  public function formatListByProduct($product){
    $list=[
      'id' => 'listReferences',
      'route' => 'referenceslist',
      'routeParams' => ["id"=>$product],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/References.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ReferencesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ReferencesTopButtons.json"),true)
    ];
    return $list;
  }


  public function getExcludedForm($params){
    return ['product'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $product=$params["product"];
    $productsRepository=$doctrine->getRepository(ERPProducts::class);
    return [
    ['product', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $productsRepository->findBy(["id"=>$product->getId()]),
      'placeholder' => 'Select a product',
      'choice_label' => function($obj, $key, $index) {
          return $obj->getName();
      },
      'choice_value' => 'id',
      'data' => $product
    ]]
  ];
  }
}
