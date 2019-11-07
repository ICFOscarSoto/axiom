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

class ERPContactsUtils
{

  private $module="ERP";
  private $name="Contacts";
  public $parentClass="\App\Modules\ERP\Entity\ERPSuppliers";
  public $parentField="supplier";

  public function formatList($user, $supplier){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "json" => "ContactsSupplier",
                        "name" => $this->name,
                        "parent" => $supplier,
                        "id" => $supplier,
                        "field" => "supplier",
                        "parentModule" => "ERP",
                        "parentName" => "Suppliers"
                      ],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ContactsSupplier.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ContactsSupplierFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ContactsSupplierTopButtons.json"),true)
    ];
    return $list;
  }
  
  public function formatListCustomers($user, $customer){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "json" => "ContactsCustomer",
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
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ContactsCustomer.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ContactsCustomerFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ContactsCustomerTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return ['supplier','customer'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $id=$params["id"];
    return [];
  }
  
  
}
