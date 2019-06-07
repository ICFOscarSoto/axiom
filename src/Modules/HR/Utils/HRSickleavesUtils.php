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


class HRSickleavesUtils
{
  public function formatListbyWorker($worker){
    $list=[
      'id' => 'listSickleaves',
      'route' => 'sickleaveslistworker',
      'routeParams' => ["id" => $worker],
      'orderColumn' => 1,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Sickleaves.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SickleavesFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SickleavesTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return ['worker','type'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $worker=$params["worker"];
    $workersRepository=$doctrine->getRepository(HRWorkers::class);
    return [
    ['worker', ChoiceType::class, [
      'required' => true,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $workersRepository->findBy(["id"=>$worker->getId()]),
      'choice_label' => function($obj, $key, $index) {
          if(method_exists($obj, "getLastname"))
            return $obj->getLastname().", ".$obj->getName();
          else return $obj->getName();
      },
      'choice_value' => 'id',
      'data' => $worker

    ]],
    ['type', ChoiceType::class, [
      'required' => true,
      'attr' => ['class' => 'select2'],
      'choices' => ['Contingencia Común'=>1, 'Contingencia Profesional'=>2],
      'placeholder' => 'Select a type...',
    ]]

  ];
  }
}
