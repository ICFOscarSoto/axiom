<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;

class ERPStocksHistoryUtils
{
  private $module="ERP";
  private $name="StocksHistory";
  public function getExcludedForm($params){
    return [];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    return [];
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListVMbyManager($manager){
    $list=[
      'id' => 'listStocksHistoryVM',
      'route' => 'StocksHistoryVMlist',
      'routeParams' => ["storemanager" => $manager],
      'orderColumn' => 3,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksHistoryVM.json"),true),
      'fieldButtons' => [],
      'topButtons' => []
    ];
    return $list;
  }

    public function formatListByManager($manager){
      $list=[
        'id' => 'listStocksHistoryManager',
        'route' => 'StocksHistoryManagerlist',
        'routeParams' => ["storemanager" => $manager],
        'orderColumn' => 2,
        'orderDirection' => 'DESC',
        'tagColumn' => 2,
        'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksHistoryManager.json"),true),
        'fieldButtons' => [],
        'topButtons' => []
      ];
      return $list;
    }

    public function formatListByUser($idUser){
      $list=[
        'id' => 'listStocksHistoryByUser',
        'route' => 'StocksHistoryByUserlist',
        'routeParams' => ["idUser" => $idUser],
        'orderColumn' => 2,
        'orderDirection' => 'DESC',
        'tagColumn' => 2,
        'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksHistoryManager.json"),true),
        'fieldButtons' => [],
        'topButtons' => []
      ];
      return $list;
    }
}
