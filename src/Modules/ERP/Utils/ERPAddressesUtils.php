<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\ERP\Entity\ERPSuppliers;

class ERPAddressesUtils
{

  public function formatListbyEntity($entity){
    $list=[
      'id' => 'listAddresses',
      'route' => 'addresslist',
      'routeParams' => ["id" => $entity],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Addresses.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/AddressesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/AddressesTopButtons.json"),true)
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
}
