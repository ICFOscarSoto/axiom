<?php
namespace App\Modules\HR\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\HR\Entity\HRWorkers;


class HRClocksUtils
{
  public function formatList($user){
    $list=[
      'id' => 'listClocks',
      'route' => 'clockslist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListbyWorker($worker){
    $list=[
      'id' => 'listClocks',
      'route' => 'clockslistworker',
      'routeParams' => ["id" => $worker],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'rowAction' => 'edit',
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListbyWorkerAdmin($worker){
    $list=[
      'id' => 'listClocks',
      'route' => 'clockslistworker',
      'routeParams' => ["id" => $worker],
      'orderColumn' => 2,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'rowAction' => 'edit',
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksFieldButtonsAdmin.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClocksTopButtonsAdmin.json"),true)
    ];
    return $list;
  }



  public function getExcludedForm($params){
    return ['worker','enddevice','startdevice'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $worker=$params["worker"];
    $workersRepository=$doctrine->getRepository(HRWorkers::class);
    return [
    ['worker', ChoiceType::class, [
      'required' => false,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $workersRepository->findBy(["id"=>$worker->getId()]),
      'placeholder' => 'Select a worker',
      'choice_label' => function($obj, $key, $index) {
          if(method_exists($obj, "getLastname"))
            return $obj->getLastname().", ".$obj->getName();
          else return $obj->getName();
      },
      'choice_value' => 'id',
      'data' => $worker

    ]]
  ];
  }
}
