<?php
namespace App\Modules\HR\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class HRMeetingsSummonedsUtils
{
  private $module="HR";
  private $name="MeetingsSummoneds";
  public $parentClass="\App\Modules\HR\Entity\HRMeetings";
  public $parentField="meeting";
  public function getExcludedForm($params){
    return ['meeting','worker'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $parent=$params["parent"];

    $em=$doctrine->getManager();
    $repository=$doctrine->getRepository("\App\Modules\HR\Entity\HRMeetingsSummoneds");
    $repositoryWorker=$doctrine->getRepository("\App\Modules\HR\Entity\HRWorkers");
    $elegibles=$repository->getElegibleSummoneds($parent, $user);
    $options=[];
    foreach($elegibles as $elegible){
      $worker=$repositoryWorker->find($elegible["id"]);
      if($worker) $options[]=$worker;
    }

    return [
    ['worker', ChoiceType::class, [
      'required' => true,
      'attr' => ['class' => 'select2', 'attr-target' => 'formWorker', 'attr-target-type' => 'full'],
      'choices' => $options,
      'placeholder' => 'Select hrworkers',
      'choice_label' => function($obj, $key, $index) {
          if(method_exists($obj, "getLastname"))
            return $obj->getLastname().", ".$obj->getName();
          else return $obj->getName();
      },
      'choice_attr' => function($obj, $key, $index) {
        return ['class' => $obj->getId()];
      },
      'choice_value' => 'id'
    ]]
  ];
  }

  public function formatList($user, $parent){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $parent,
                        "id" => $parent,
                        "field" => "meeting",
                        "parentModule" => "HR",
                        "parentName" => "Meetings"
                      ],
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
