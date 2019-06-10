<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\GlobaleEmailAccounts;

class GlobaleQuotationGroupsUtils
{
  public function getExcludedForm($params){
    return ['periodsalary'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];

    return [
      ['periodsalary', ChoiceType::class, [
        'required' => true,
        'attr' => ['class' => 'select2'],
        'choices' => ['Mensual'=>'month', 'Diario'=>"day"],
        'placeholder' => 'Select a type...'
      ]]
    ];
  }

  public function formatList($user){
    $list=[
      'id' => 'listQuotationGroups',
      'route' => 'genericlist',
      'routeParams' => ["module" => "Globale",
                        "name" => "QuotationGroups"],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/QuotationGroups.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/QuotationGroupsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/QuotationGroupsTopButtons.json"),true)
    ];
    return $list;
  }
}
