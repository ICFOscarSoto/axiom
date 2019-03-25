<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class ERPWebProductsUtils
{
  public function formatList($user){
    $list=[
      'id' => 'listWebProducts',
      'route' => 'webproductlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProducts.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProductsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProductsTopButtons.json"),true)
    ];
    return $list;
  }
}
