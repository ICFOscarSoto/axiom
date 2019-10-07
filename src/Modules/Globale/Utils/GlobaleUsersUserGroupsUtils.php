<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class GlobaleUsersUserGroupsUtils
{
  private $module="Globale";
  private $name="UsersUserGroups";
  public $parentClass="\App\Modules\Globale\Entity\GlobaleUsers";
  public $parentField="user";
  public function getExcludedForm($params){
    return ["user", "usergroup"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];


    $em=$doctrine->getManager();
    $results=$em->createQueryBuilder()->select('u')
      ->from('App\Modules\Globale\Entity\GlobaleUserGroups', 'u')
      ->leftJoin('App\Modules\Globale\Entity\GlobaleUsersUserGroups', 'g', 'WITH', 'u.id = g.usergroup')
      ->where('g.id IS NULL')
      ->orWhere('g.deleted = 1')
      ->orWhere('g.id = :val_user')
      ->andWhere('u.company = :val_company')
      ->setParameter('val_user', $id)
      ->setParameter('val_company', $user->getCompany())
      ->getQuery()
      ->getResult();

    return [
    ['usergroup', ChoiceType::class, [
      'required' => false,
      'attr' => ['class' => 'select2'],
      'choices' => $results,
      'placeholder' => 'Select a usergroup',
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
