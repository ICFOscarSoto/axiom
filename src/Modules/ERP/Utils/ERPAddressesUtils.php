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
use App\Modules\ERP\Entity\ERPContacts;
use App\Modules\ERP\Entity\ERPAddresses;

class ERPAddressesUtils
{

  private $module="ERP";
  private $name="Addresses";
  public $parentClass="\App\Modules\ERP\Entity\ERPCustomers";
  public $parentClassCustomerAddresses="\App\Modules\ERP\Entity\ERPCustomers";
  public $parentField="customer";
  public $parentFieldCustomerAddresses="customer";

  public function formatListByCustomer($user, $customer){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "json" => "CustomerAddresses",
                        "name" => $this->name,
                        "parent" => $customer,
                        "id" => $customer,
                        "field" => "customer",
                        "parentModule" => "ERP",
                        "parentName" => "Customers"
                      ],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerAddresses.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerAddressesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerAddressesTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return [];
  }

  public function getIncludedForm($params){
      $doctrine=$params["doctrine"];
      $user=$params["user"];
      $id=$params["id"];
      return [];
  }

  public function getExcludedFormCustomerAddresses($params){
    return ['supplier','customer','contact'];
  }

  public function getIncludedFormCustomerAddresses($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $id=$params["id"];
    $contactsRepository=$doctrine->getRepository(ERPContacts::class);
    //$addressesRepository=$doctrine->getRepository(ERPAddresses::class);
    //$address=$addressesRepository->findOneBy(["id"=>$id]);
    //$customer_id=$address->getCustomer()->getId();
      return [['contact', ChoiceType::class, [
        'required' => false,
        'attr' => ['class' => 'select2'],
        'choices' => $contactsRepository->findBy(["customer"=>$params["parent"]]),
        'placeholder' => 'Select a contact...',
        'choice_label' => 'name',
        'choice_value' => 'id'
      ]
      ]];

  }
}
