<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Modules\Globale\Entity\MenuOptions;

class ERPCustomersUtils
{

  public function getIncludedForm($params){
    return [['entityName', TextType::class, ['data'=>$params->getName(),'mapped'=>false,'attr'=>['class' => 'tagsinput']]]];
  }

  public function formatList($user){
    $list=[
      'id' => 'listCustomers',
      'route' => 'customerlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Customers.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomersFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomersTopButtons.json"),true)
    ];
    return $list;
  }
}
