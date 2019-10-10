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

  public function formatListbyEntity($entity){
    $list=[
      'id' => 'listBankAccounts',
      'route' => 'bankaccountlist',
      'routeParams' => ["id" => $entity],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../../ERP/Lists/BankAccounts.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../../ERP/Lists/BankAccountsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../../ERP/Lists/BankAccountsTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return [];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    return [];
  }
}
