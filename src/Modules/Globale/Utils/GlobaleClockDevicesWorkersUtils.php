<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleClockDevices;

class GlobaleClockDevicesWorkersUtils
{
  private $module="Globale";
  private $name="ClockDevicesWorkers";
  public function getExcludedForm($params){
    return ['clockdevice'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $clockdevice=$params["clockdevice"];
    $repository=$doctrine->getRepository(GlobaleClockDevices::class);
    return [['clockdevice', ChoiceType::class, [
      'required' => true,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $repository->findBy(["id"=>$clockdevice->getId(), "active"=>1, "deleted"=>0]),
      'choice_label' => function($obj, $key, $index) {
          return $obj->getIdentifier();
      },
      'choice_value' => 'id',
      'data' => $clockdevice
    ]]
  ];
  }

  public function formatList($id){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'clockdevicesworkerslist',
      'routeParams' => ["id" => $id],
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
