<?php

namespace App\Controller\Globale;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Globale\MenuOptions;
use App\Entity\Globale\Companies;
use App\Entity\Globale\Countries;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Utils\Globale\ListUtils;
use App\Utils\Globale\EntityUtils;

class CompaniesController extends Controller
{
	private $listFields=array(array("name" => "id", "caption"=>""), array("name" => "vat", "caption"=>"CIF/NIF", "width" => "10%"), array("name" =>"name","caption"=>"Razón Social"),
								 array("name" => "active", "caption"=>"Estado", "width"=>"10%" ,"class" => "dt-center", "replace"=>array("1"=>"<div style=\"min-width: 75px;\" class=\"label label-success\">Activo</div>",
																																																		"0" => "<div style=\"min-width: 75px;\" class=\"label label-danger\">Desactivado</div>"))
								);
	 private $class=Companies::class;

    /**
     * @Route("/{_locale}/admin/global/companies", name="companies")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);

		$templateLists=array();
		$listCompanies=array();
		$listCompanies['id'] = 'listCompanies';
		$listCompanies['fields'] = $this->listFields;
		$listCompanies['route'] = 'companieslist';
		$listCompanies['orderColumn'] = 2;
		$listCompanies['orderDirection'] = 'DESC';
		$listCompanies['tagColumn'] = 3;
		$listCompanies['fieldButtons'] = array(
			array("id" => "edit", "type" => "default", "icon" => "fa fa-edit", "name" => "editar", "route"=>"editCompany", "confirm" =>false, "actionType" => "foreground"),
			array("id" => "desactivate", "type" => "info", "condition"=> "active", "conditionValue" =>true , "icon" => "fa fa-eye-slash","name" => "desactivar", "route"=>"disableCompany", "confirm" =>true, "actionType" => "background" ),
			array("id" => "activate", "type" => "info", "condition"=> "active", "conditionValue" =>false, "icon" => "fa fa-eye","name" => "activar", "route"=>"enableCompany", "confirm" =>true, "actionType" => "background" ),
			array("id" => "delete", "type" => "danger", "icon" => "fa fa-trash","name" => "borrar", "route"=>"editCompany", "confirm" =>true, "undo" =>false, "tooltip"=>"Borrar empresa", "actionType" => "background")
					/*array("id" => "active", "condition" => array(
														array("id" => "desactivate", "type" => "info", "icon" => "fa fa-eye-slash","name" => "desactivar", "route"=>"disableCompany", "confirm" =>true, "actionType" => "background" ),
														array("id" => "activate", "type" => "info", "icon" => "fa fa-eye","name" => "activar", "route"=>"enableCompany", "confirm" =>true, "actionType" => "background" )
														)),*/
		);
		$listCompanies['topButtons'] = array(
			array("id" => "addTop", "type" => "btn-primary", "icon" => "fa fa-plus", "name" => "", "route"=>"new_company", "confirm" =>false, "tooltip" => "Crear nueva empresa"),
			array("id" => "deleteTop", "type" => "btn-red", "icon" => "fa fa-trash","name" => "", "route"=>"editCompany", "confirm" =>true),
			array("id" => "printTop", "type" => "", "icon" => "fa fa-print","name" => "", "route"=>"editCompany", "confirm" =>false),
			array("id" => "exportTop", "type" => "", "icon" => "fa fa-file-excel-o","name" => "", "route"=>"editCompany", "confirm" =>false)
		);
		$templateLists[]=$listCompanies;
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('genericlist.html.twig', [
				'controllerName' => 'CompaniesController',
				'interfaceName' => 'Empresas',
				'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
    }

	/**
	 * @Route("/api/companies/list", name="companieslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new ListUtils();
		$return=$listUtils->getRecords($repository,$request,$manager,$this->listFields, $this->class);
		return new JsonResponse($return);
	}

	/**
	* @Route("/{_locale}/admin/global/companies/new", name="new_company")
	*/
	public function form(Request $request)
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();

		$locale = $request->getLocale();
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
        $company = new Companies();

        // Array de configuración de formulario
        $doctrine=$this->getDoctrine()->getManager()->getMetadataFactory()->getMetadataFor($this->class);



        $form["id"]= "";
        $form["name"] = "form_companies";
        $form["route"] = "new_company";
				$form["label"] = "Company";
        $form["section"] = array(
							array(
								 "label" => "General Data",
								 "size" => "md-12",
			        	 "rows" => array(
								 					array(
				        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "name", "caption" => "Name" , "type" => "input","icon" => "","size" => "md-6", "metadata" => serialize($doctrine->fieldMappings['name'])),
				        	 					array( "name"=>"Social Name","placeHolder"=>"Social Name","id" => "socialname","caption" => "Social Name" ,"icon" => "", "type" => "input","size" =>"md-6", "metadata" => serialize($doctrine->fieldMappings['socialname'])),
														array( "name"=>"CIF/NIF","placeHolder"=>"","id" => "vat", "caption" => "CIF/NIF" , "type" => "input", "icon" => "","size" => "md-2", "metadata" => serialize($doctrine->fieldMappings['vat']))
													),
													array(
				        	 					array( "name"=>"Address","id" => "address", "caption" => "Address" , "type" => "input", "icon" => "","size"=>"md-3", "metadata" => serialize($doctrine->fieldMappings['address'])),
				        	 					array( "name"=>"City","id" => "city", "caption" => "City" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['city'])),
				        	 					array( "name"=>"State","id" => "state", "caption" => "State" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['state']))
													),
													array(
				        	 					array( "name"=>"Postcode","id" => "postcode", "caption" => "Postcode" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['postcode'])),
				        	 					array( "name"=>"Phone","id" => "phone", "caption" => "Phone" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['phone'])),
				        	 					array( "name"=>"Mobile","id" => "mobile", "caption" => "Mobile" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['mobile']))
													),
													array(
				        	 					//array( "id" => "currency", "caption" => "Currency" , "type" => "select", "icon" => "", "metadata" => serialize($doctrine->fieldMappings['currency'])),
				        	 					array( "name"=>"Date Add","id" => "dateadd", "caption" => "Date Add" , "type" => "text", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['dateadd'])),
				        	 					array( "name"=>"Last Update","placeHolder"=>"Name","id" => "dateupd", "caption" => "Last Update" , "type" => "text", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['dateupd'])),
				        	 					array( "name"=>"Status ","id" => "active", "caption" => "Status" , "type" => "checkbox", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['active'])),
				        	 					array( "name"=>"Deleted","placeHolder"=>"Name","id" => "deleted", "caption" => "Deleted" , "type" => "checkbox", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['deleted']))
													)
												)
											),
											array(
												 "label" => "General Data",
							        	 "rows" => array(
												 					array(
								        	 					array( "name"=>"Date Add","name"=>"Name","placeHolder"=>"Name","id" => "name", "caption" => "Name" , "type" => "file","icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['name'])),
								        	 					array( "name"=>"Date Add","id" => "socialname", "caption" => "Social Name" ,"icon" => "", "type" => "textarea","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['socialname'])),
																		array( "name"=>"Date Add","id" => "vat", "caption" => "CIF/NIF" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['vat']))
																	),
																	array(
								        	 					array( "name"=>"Namex","placeHolder"=>"Namex","id" => "address", "caption" => "Address" , "type" => "datepicker","dataformat"=>"D, dd MM yyyy", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['address'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "city", "caption" => "City" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['city'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "state", "caption" => "State" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['state']))
																	),
																	array(
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "postcode", "caption" => "Postcode" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['postcode'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "phone", "caption" => "Phone" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['phone'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "mobile", "caption" => "Mobile" , "type" => "input", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['mobile']))
																	),
																	array(
								        	 					//array( "id" => "currency", "caption" => "Currency" , "type" => "select", "icon" => "", "metadata" => serialize($doctrine->fieldMappings['currency'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "dateadd", "caption" => "Date Add" , "type" => "text", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['dateadd'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "dateupd", "caption" => "Last Update" , "type" => "text", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['dateupd'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "active", "caption" => "Status" , "type" => "checkbox", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['active'])),
								        	 					array( "name"=>"Name","placeHolder"=>"Name","id" => "deleted", "caption" => "Deleted" , "type" => "checkbox", "icon" => "","size" => "md-3", "metadata" => serialize($doctrine->fieldMappings['deleted']))
																	)
																)
															)

					);



				dump($form);

		$new_breadcrumb["rute"]=null;
		$new_breadcrumb["name"]="Nueva";
		$new_breadcrumb["icon"]="fa fa-plus";
		$breadcrumb=$menurepository->formatBreadcrumb('companies');
		$countries=$this->getDoctrine()->getRepository(Countries::class);
		array_push($breadcrumb, $new_breadcrumb);
		$formlist[] = $form;
        return $this->render('Globale/newcompany.html.twig', array(
            'controllerName' => 'CompaniesController',
            'interfaceName' => 'Empresas',
			'optionSelected' => $request->attributes->get('_route'),
			'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
			'breadcrumb' =>  $breadcrumb,
			'userData' => $userdata,
			'formData' => $form,

        ));
    }

	/**
	* @Route("/{_locale}/admin/global/companies/{id}/edit", name="editCompany")
	*/
	public function editCompany($id,Request $request)
    {
		return $this->redirectToRoute('/{_locale}/admin/global/companies');
	}


	/**
	* @Route("/{_locale}/admin/global/companies/{id}/disable", name="disableCompany")
	*/
	public function disableCompany($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/companies/{id}/enable", name="enableCompany")
	*/
	public function enableCompany($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
