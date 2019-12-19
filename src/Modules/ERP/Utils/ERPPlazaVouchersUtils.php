<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPPlazaVouchers;

class ERPPlazaVouchersUtils
{
  private $module="ERP";
  private $name="PlazaVouchers";
  public function getExcludedForm($params){
    return ['user'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $repositoryUser=$doctrine->getRepository(GlobaleUsers::class);
    $repository=$doctrine->getRepository(ERPPlazaVouchers::class);
    $obj=$repository->findOneBy(["id"=>$id]);
    $user=$repositoryUser->findBy(["id"=>($id>0?$obj->getUser()->getId():$user->getId())]);
    return [['dateadd', DateTimeType::class, [
      'required' => false,
      'widget' => 'single_text',
      'disabled' => true,
      'format' => 'dd/MM/yyyy kk:mm:ss',
      'attr'=> ["readonly"=>true,'class' => 'datetimepicker']
    ]],
    ['user', ChoiceType::class, [
      'required' => true,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $user,
      'choice_label' => function($obj, $key, $index) {
          if(method_exists($obj, "getLastname") && $obj->getLastname()!=null)
            return $obj->getLastname().", ".$obj->getName();
          else return $obj->getName();
      },
      'data' => $user,
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
