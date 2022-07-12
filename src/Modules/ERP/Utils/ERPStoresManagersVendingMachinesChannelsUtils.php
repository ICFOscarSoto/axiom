<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPPlazaVouchers;

class ERPStoresManagersVendingMachinesChannelsUtils
{
  private $module="ERP";
  private $name="StoresManagersVendingMachinesChannels";
  public function getExcludedForm($params){
    //return ['user'];
    return ["vendingmachine"];
  }

  public function getIncludedForm($params){
    return [
      ['vendingmachine',HiddenType::class, ['mapped'=>false, 'data' => $params["vendingmachine"]->getId()]]
    ];
  }

  public function formatList($vendingmachine){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'vendingmachinechannels',
      'routeParams' => ["id" => $vendingmachine],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];

    return $list;
  }
}
