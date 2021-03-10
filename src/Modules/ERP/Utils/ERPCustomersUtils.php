<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\ERP\Entity\ERPContacts;

class ERPCustomersUtils
{
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


  public function formatListWithCode($user){
    $list=[
      'id' => 'listCustomerswithcode',
      'route' => 'customerlistwithcode',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomersWithCode.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomersFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomersTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return ['customer','maincontact'];
  }

  public function getIncludedForm($params){

  $doctrine=$params["doctrine"];
  $user=$params["user"];
  $id=$params["id"];
  $contactsRepository=$doctrine->getRepository(ERPContacts::class);

  return [['maincontact', ChoiceType::class, [
    'required' => false,
    'attr' => ['class' => 'select2'],
    'choices' => $contactsRepository->findBy(["customer"=>$id]),
    'placeholder' => 'Select a contact...',
    'choice_label' => 'name',
    'choice_value' => 'id'
  ]
  ]];
}








}
