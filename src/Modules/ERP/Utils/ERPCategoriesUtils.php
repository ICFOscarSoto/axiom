<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\ERP\Entity\ERPCategories;

class ERPCategoriesUtils
{

  public function formatList($user){
    $list=[
      'id' => 'listCategories',
      'route' => 'genericlist',
      'routeParams' => ["module" => "ERP",
                        "name" => "categories"],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Categories.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CategoriesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CategoriesTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return ['parentid'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $parent=$params["parentid_id"];
    $categoriesRepository=$doctrine->getRepository(ERPCategories::class);
    return [
    ['parentid', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $categoriesRepository->findBy(['id'=>$parent->getId()]),
      'placeholder' => 'Select a category',
      'choice_label' => function($obj, $key, $index) {
          return $obj->getName();
      },
      'choice_value' => 'id',
      'data' => $parent
    ]]
  ];
  }

}
