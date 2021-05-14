<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class ERPStoreLocationsUtils
{
  private $module="ERP";
  private $name="StoreLocations";
  public function getExcludedForm($params){
    return ["store"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $store=$params["store"];
    return [];
  }

  public function formatListByStore($store){
    $list=[
      'id' => 'listLocations',
      'route' => 'storelocationlist',
      'routeParams' => ["id" => $store],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoreLocations.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoreLocationsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoreLocationsTopButtons.json"),true)
    ];
    return $list;
  }

}
