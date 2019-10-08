<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class GlobaleCompaniesModulesUtils
{
  private $module="Globale";
  private $name="CompaniesModules";
  public $parentClass="\App\Modules\Globale\Entity\GlobaleCompanies";
  public $parentField="companyown";
  public function getExcludedForm($params){
    return ['companyown','module'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $parent=$params["parent"];

    $em=$doctrine->getManager();
    $qb = $em->createQueryBuilder();
    $query="SELECT module_id	FROM  globale_companies_modules WHERE companyown_id=:val_company AND deleted=0";
    $params=['val_company' => $parent->getId()];
    $selected=$doctrine->getConnection()->executeQuery($query, $params)->fetchAll();
    $query = $qb->select('rl')
                 ->from('App\Modules\Globale\Entity\GlobaleModules', 'rl')
                 ->andWhere('rl.active=1 AND rl.deleted=0')
                 ->andWhere('rl.id <> 1');

     if(count($selected)) $query->andWhere($qb->expr()->notIn('rl.id', array_column($selected,'module_id')));
     $results=$query->getQuery()
                    ->getResult();

    return [
    ['module', ChoiceType::class, [
      'required' => false,
      'attr' => ['class' => 'select2'],
      'choices' => $results,
      'placeholder' => 'Select a module',
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
