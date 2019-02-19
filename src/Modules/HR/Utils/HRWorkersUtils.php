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
use App\Modules\Cloud\Utils\CloudFilesUtils;


class HRWorkersUtils extends Controller
{
  public function formatEditor($user, $obj, $request, $controller, $doctrine, $router, $name, $icon){
    $userdata=$user->getTemplateData();
    $new_breadcrumb["rute"]=null;
    $new_breadcrumb["name"]=$name;
    $new_breadcrumb["icon"]=$icon;
    $menurepository=$doctrine->getRepository(MenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('workers');
    $form=$this->formatForm($user, $obj, $request, $controller, $doctrine);

    array_push($breadcrumb, $new_breadcrumb);
    $cloudLists[]=CloudFilesUtils::formatList($user);
    return ['template'=>'@HR/formworker.html.twig', 'vars'=>array(
        'controllerName' => 'WorkersController',
        'interfaceName' => 'Trabajadores',
        'optionSelected' => 'workers',
        'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
        'breadcrumb' =>  $breadcrumb,
        'userData' => $userdata,
        'formworker' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Workers.json"),true)],
        'listDocuments' => ["list" => $cloudLists, "path" => $router->generate('cloudUpload', array('path'=>'workers', 'id'=>$obj->getId()))]
    )];
  }

  public function formatForm($user, $obj, $request, $controller, $doctrine){
    $formUtils=new FormUtils();
    $formUtils->init($doctrine,$request);
    $form=$formUtils->createFromEntity($obj,$controller,['status'],[
      ['status', ChoiceType::class, [
        'required' => false,
        'attr' => ['class' => 'select2'],
        'choices' => ["Inactive"=>0,"Active"=>1,"Sick leave"=>2],
        'placeholder' => 'Select an status',
      ]]

    ])->getForm();
    return $form;
  }

  public function formatList($user){
		$list=[
			'id' => 'listWorkers',
			'route' => 'workerslist',
			'routeParams' => ["id" => $user->getId()],
			'orderColumn' => 1,
			'orderDirection' => 'ASC',
			'tagColumn' => 1,
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Workers.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersTopButtons.json"),true)
		];
		return $list;
	}
}
