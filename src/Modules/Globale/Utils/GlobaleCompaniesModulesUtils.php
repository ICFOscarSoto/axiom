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
    $em=$doctrine->getManager();
    $results=$em->createQueryBuilder()->select('u')
      ->from('App\Modules\Globale\Entity\GlobaleModules', 'u')
      ->leftJoin('App\Modules\Globale\Entity\GlobaleCompaniesModules', 'g', 'WITH', 'u.id = g.module')
      ->where('g.id IS NULL')
      ->orWhere('g.deleted = 1')
      ->orWhere('g.id = :val_id')
      ->andWhere('u.id <> 1')
      ->setParameter('val_id', $id)
      ->getQuery()
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
