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

class HRWorkCalendarsUtils extends Controller
{
  public function formatEditor($user, $obj, $request, $controller, $doctrine, $router, $name, $icon){
    $userdata=$user->getTemplateData();
    $new_breadcrumb["rute"]=null;
    $new_breadcrumb["name"]=$name;
    $new_breadcrumb["icon"]=$icon;
    $menurepository=$doctrine->getRepository(MenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('workcalendars');
    $form=$this->formatForm($user, $obj, $request, $controller, $doctrine);

    array_push($breadcrumb, $new_breadcrumb);
    return ['template'=>'@Globale/genericform.html.twig', 'vars'=>array(
        'controllerName' => 'WorkCalendarsController',
        'interfaceName' => 'Calendarios laborales',
        'optionSelected' => 'workcalendars',
        'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
        'breadcrumb' =>  $breadcrumb,
        'userData' => $userdata,
        'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/WorkCalendars.json"),true)]
    )];
  }
/*
  public function formatEditorAjax($user, $obj, $request, $controller, $doctrine, $ajax=){
    $formUtils=new FormUtils();
    $formUtils->init($doctrine,$request);
    $form=$formUtils->createFromEntity($obj,$controller,[],[],false)->getForm();
    $proccess=$formUtils->proccess($form,$obj);
    if($proccess===FALSE) return ["id"=>"workcalendar", "form" => $form->createView(), "template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/WorkCalendars.json"),true)];
      else return true;
  }
*/

  public function formatForm($user, $obj, $request, $controller, $doctrine, $ajax=false){
    $formUtils=new FormUtils();
    $formUtils->init($doctrine,$request);
    $form=$formUtils->createFromEntity($obj,$controller,[],[],!$ajax)->getForm();
    $proccess=$formUtils->proccess($form,$obj);
    if($ajax){
      if($proccess===FALSE) return ["id"=>"workcalendar", "form" => $form->createView(), "template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/WorkCalendars.json"),true)];
        else return $proccess;
    }else return $form;
  }

  public function formatList($user){
    $list=[
      'id' => 'listWorkCalendars',
      'route' => 'workcalendarslist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkCalendars.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkCalendarsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkCalendarsTopButtons.json"),true)
    ];
    return $list;
  }

}
