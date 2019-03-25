<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class ERPProductVariantsUtils
{
  public function formatList($user){
    $list=[
      'id' => 'listProductVariants',
      'route' => 'productvariantlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariants.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariantsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariantsTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListbyProduct($product){
    $list=[
      'id' => 'listProductVariants',
      'route' => 'productvariantslistproduct',
      'routeParams' => ["id" => $product],
      'orderColumn' => 4,
      'orderDirection' => 'DESC',
      'tagColumn' => 4,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariants.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariantsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariantsTopButtons.json"),true)
    ];
    return $list;
  }
}
