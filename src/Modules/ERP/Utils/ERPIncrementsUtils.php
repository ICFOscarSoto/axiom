<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPSuppliers;

class ERPIncrementsUtils
{
  private $module="ERP";
  private $name="Increments";
  
  public function formatListbyEntity($entity){
    $list=[
      'id' => 'listIncrements',
      'route' => 'supplierincrementlist',
      'routeParams' => ["id" => $entity],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/IncrementsSupplier.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/IncrementsSupplierFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/IncrementsSupplierTopButtons.json"),true)
    ];
    return $list;
  }
  
  public function getExcludedForm($params){
    return ['supplier'];
  }
  
  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $supplier=$params["supplier"];
    $suppliersRepository=$doctrine->getRepository(ERPSuppliers::class);
    return [
    ['supplier', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $suppliersRepository->findBy(["id"=>$supplier->getId()]),
      'placeholder' => 'Select a supplier',
      'choice_label' => function($obj, $key, $index) {
          return $obj->getSocialname();
      },
      'choice_value' => 'id',
      'data' => $supplier
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
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Increments.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/IncrementsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/IncrementsTopButtons.json"),true)
    ];
    return $list;
  }
}
