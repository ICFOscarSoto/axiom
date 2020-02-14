<?php
namespace App\Modules\HR\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\HR\Entity\HREquipmentCategories;

class HREquipmentsUtils
{
  private $module="HR";
  private $name="Equipments";
  public function getExcludedForm($params){
    return ['category'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $equipmentsCategoriesRepository=$doctrine->getRepository(HREquipmentCategories::class);
    return [
    ['category', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $equipmentsCategoriesRepository->findBy(["active"=>1 ,"deleted"=>0], ["parent"=> "ASC", "name"=>"ASC"]),
      'placeholder' => 'Select a category',
      'choice_label' => function($obj, $key, $index) {
        if($obj->getParent())
          return $obj->getParent()->getName().' > '.$obj->getName();
          else   return $obj->getName();
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
