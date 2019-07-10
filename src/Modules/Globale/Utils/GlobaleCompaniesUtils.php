<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleAgents;
use App\Modules\Email\Entity\GlobaleEmailAccounts;

class GlobaleCompaniesUtils
{
public function getExcludedForm(){
  return ['bankaccount','agent'];
}

  public function formatList($user){
    $list=[
      'id' => 'listCompanies',
      'route' => 'companieslist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Companies.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CompaniesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CompaniesTopButtons.json"),true)
    ];
    return $list;
  }

  public function getIncludedForm($params){
      $doctrine=$params["doctrine"];
      $user=$params["user"];
      $agentsRepository=$doctrine->getRepository(GlobaleAgents::class);
      return [
      ['agent', ChoiceType::class, [
        'required' => false,
        'disabled' => false,
        'attr' => ['class' => 'select2', 'readonly' => true],
        'choices' => $agentsRepository->findBy(["company"=>$user->getCompany()->getId()]),
        'placeholder' => 'Select an agent',
        'choice_label' => function($obj, $key, $index) {
            if(method_exists($obj, "getLastname") && $obj->getLastname()!=null)
              return $obj->getLastname().", ".$obj->getName();
            else return $obj->getName();
        },
        'choice_value' => 'id'

      ]]
    ];
  }
}
