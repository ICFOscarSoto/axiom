<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class ERPBankAccountsUtils
{
  private $module="ERP";
  private $name="BankAccounts";
/*
  public $parentClass="\App\Modules\ERP\Entity\ERPSuppliers";
  public $parentField="supplier";
*/
  public function formatListbySupplier($id, $supplier){
/*
    $bankaccountsRepository=$doctrine->getRepository('App\\Modules\\ERP\\Entity\\ERPBankAccounts');
    $bankaccount=$bankaccountsRepository->findOneBy(["supplier"=>$supplier]);
*/
  /*
    if($bankaccount)
    {
*/
    $list=[
      'id' => 'listBankAccounts',
      'route' => 'bankaccountlist',
      /*'routeParams' => ["id" => $entity],*/
      'routeParams' => ["id" => $id, "supplierid" => $supplier],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../../ERP/Lists/BankAccounts.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../../ERP/Lists/BankAccountsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../../ERP/Lists/BankAccountsTopButtons.json"),true)
    ];
    return $list;
    //}

  }

  public function getExcludedForm($params){
    return ["customer","supplier"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    return [];
  }
}
