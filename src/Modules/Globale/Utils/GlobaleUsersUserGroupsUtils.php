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
    $parent=$params["parent"];

    $user=$params["user"];
    $em=$doctrine->getManager();
    $qb = $em->createQueryBuilder();
    $query="SELECT usergroup_id	FROM  globale_users_user_groups WHERE user_id=:val_user AND deleted=0";
    $params=['val_user' => $parent->getId()];
    $selected=$doctrine->getConnection()->executeQuery($query, $params)->fetchAll();
    //array_merge($selected,["usergroup_id"=>0]);
    $query = $qb->select('rl')
                 ->from('App\Modules\Globale\Entity\GlobaleUserGroups', 'rl')
                 ->andWhere('rl.active=1 AND rl.deleted=0 AND rl.company=:val_company')
                 ->setParameter('val_company', $user->getCompany());
    if(count($selected)) $query->andWhere($qb->expr()->notIn('rl.id', array_column($selected,'usergroup_id')));
    $results=$query->getQuery()
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


  public function formatList($user, $id){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name,
                        "parent" => $id,
                        "id" => $id,
                        "field" => "user",
                        "parentModule" => "Globale",
                        "parentName" => "Users"
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
