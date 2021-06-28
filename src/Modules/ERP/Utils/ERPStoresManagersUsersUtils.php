<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use App\Modules\Globale\Entity\GlobaleUsers;

class ERPStoresManagersUsersUtils
{
  private $module="ERP";
  public $parentClass="\App\Modules\ERP\Entity\ERPStoresManagers";
  public $parentField="manager";
  private $name="StoresManagersUsers";
  public function getExcludedForm($params){
    //return ['manager'];
    return['manager','user'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $repository=$doctrine->getRepository(ERPStoresManagersUsers::class);
    $repositoryUser=$doctrine->getRepository(GlobaleUsers::class);
    $parent=$params["parent"];
    $idsusers=$repository->getElegibleUsers($params["parent"], $user);
    $users=[];
    foreach($idsusers as $iduser){
      $users[]=$repositoryUser->findOneBy(["id"=>$iduser]);
    }
    //dump($users);
    return [['user', ChoiceType::class, [
      'required' => true,
      'attr' => ['class' => 'select2'],
      'choices' => $users,
      'placeholder' => 'Select a user...',
      'choice_label' => function($obj, $key, $index) {
          return $obj->getName().' '.$obj->getLastname();
      },
      'choice_value' => 'id'
    ]
    ]];
    //return[];
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
