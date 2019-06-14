<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCompanies;

class GlobaleClockDevicesUtils
{
  private $module="Globale";
  private $name="ClockDevices";
  public function getExcludedForm($params){
    return ['company'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $companyRepository=$doctrine->getRepository(GlobaleCompanies::class);
    return [['company', ChoiceType::class, [
      'required' => true,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => false],
      'choices' => $companyRepository->findBy(["active"=>1, "deleted"=>0]),
      'choice_label' => function($obj, $key, $index) {
          if(method_exists($obj, "getLastname"))
            return $obj->getLastname().", ".$obj->getName();
          else return $obj->getName();
      },
      'choice_value' => 'id'
    ]]
  ];
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name],
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
