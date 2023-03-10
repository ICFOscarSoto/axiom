<?php
namespace App\Modules\AERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\AERP\Entity\AERPCustomerContacts;

class AERPCustomersUtils
{
  private $module="AERP";
  private $name="Customers";
  public function getExcludedForm($params){
    return ['shippcontact'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $obj=$params["obj"];
    $repository=$doctrine->getRepository(AERPCustomerContacts::class);
    return [
      ['shippcontact', ChoiceType::class, [
        'required' => false,
        'disabled' => false,
        'attr' => ['class' => 'select2', 'readonly' => true],
        'choices' => $repository->findBy(["customer"=>$obj]),
        'placeholder' => 'Select aerpcustomercontacts',
        'choice_label' => function($obj, $key, $index) {
            if(method_exists($obj, "getLastname") && $obj->getLastname()!=null)
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
