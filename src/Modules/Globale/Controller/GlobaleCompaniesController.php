<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleCompaniesUtils;
use App\Modules\Globale\Reports\GlobaleSEPAReports;
use App\Modules\ERP\Utils\ERPBankAccountsUtils;
use App\Modules\ERP\Entity\ERPBankAccounts;
use App\Modules\Cloud\Utils\CloudFilesUtils;
//use App\Modules\Globale\UtilsEntityUtils;
//use App\Modules\Form\Controller\FormController;

class GlobaleCompaniesController extends Controller
{

	 private $class=GlobaleCompanies::class;
	 private $utilsClass=GlobaleCompaniesUtils::class;



	 /**
	  * @Route("/{_locale}/globale/company/form/{id}", name="formCompany", defaults={"id"=0})
	  */
	  public function formCompany($id, Request $request){
	 	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	$this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 	$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
	 	$template=dirname(__FILE__)."/../Forms/Companies.json";
	 	$userdata=$this->getUser()->getTemplateData();
	 	$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	 	$breadcrumb=$menurepository->formatBreadcrumb('companies');
	 	array_push($breadcrumb, $new_breadcrumb);
	 	$repository=$this->getDoctrine()->getRepository($this->class);
	 	$obj = $repository->findOneBy(['id'=>$id, 'deleted'=>0]);
	 	$entity_name=$obj?$obj->getSocialname().' ('.$obj->getVat().')':'';
	 	return $this->render('@Globale/generictabform.html.twig', array(
	 					'entity_name' => $entity_name,
	 					'controllerName' => 'CompaniesController',
	 					'interfaceName' => 'Empresas',
	 					'optionSelected' => 'companies',
	 					'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
	 					'breadcrumb' => $breadcrumb,
	 					'userData' => $userdata,
	 					'id' => $id,
	 					'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
	 					'tabs' => [["name" => "data", "caption"=>"Datos empresa", "icon"=>"entypo-book-open","active"=>true, "route"=>$this->generateUrl("dataCompanyAdmin",["id"=>$id])],
	 										 ["name" => "bank", "icon"=>"fa fa-headphones", "caption"=>"Datos bancarios", "route"=>$this->generateUrl("dataCompanyBankAccounts",["identity"=>$id,"id"=>$obj?($obj->getBankaccount()?$obj->getBankaccount()->getId():0):0])],
	 										 ["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"companies"])]
	 										],
	 					'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
	 					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
	 															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
	 					/*'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>$tab=='data'?true:false, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
	 										 ["name" => "paymentroll", "active"=>($tab=='paymentroll' && $id)?true:false, "caption"=>"Nóminas"]
	 										]*/
	 	));
	 }

	 /**
	  * @Route("/{_locale}/globale/admin/mycompany", name="mycompany")
	  */
	  public function mycompany(Request $request){
	  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	  $this->denyAccessUnlessGranted('ROLE_ADMIN');
		$id=$this->getUser()->getCompany()->getId();
	  $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
	  $template=dirname(__FILE__)."/../Forms/CompaniesAdmin.json";
	  $userdata=$this->getUser()->getTemplateData();
	  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	  $breadcrumb=$menurepository->formatBreadcrumb('mycompany');
	  array_push($breadcrumb, $new_breadcrumb);
	  $repository=$this->getDoctrine()->getRepository($this->class);
	  $obj = $repository->findOneBy(['id'=>$id, 'deleted'=>0]);
	  $entity_name=$obj?$obj->getSocialname().' ('.$obj->getVat().')':'';
	  return $this->render('@Globale/generictabform.html.twig', array(
	 				 'entity_name' => $entity_name,
	 				 'controllerName' => 'CompaniesController',
	 				 'interfaceName' => 'Mi Empresa',
	 				 'optionSelected' => 'mycompany',
	 				 'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
	 				 'breadcrumb' => $breadcrumb,
	 				 'userData' => $userdata,
	 				 'id' => $id,
	 				 'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
	 				 'tabs' => [["name" => "data", "caption"=>"Datos empresa", "icon"=>"entypo-book-open","active"=>true, "route"=>$this->generateUrl("dataCompanyAdmin",["id"=>$id])],
					 						["name" => "bank", "icon"=>"fa fa-headphones", "caption"=>"Datos bancarios", "route"=>$this->generateUrl("dataMyCompanyBankAccounts",["identity"=>$id,"id"=>$obj?($obj->getBankaccount()?$obj->getBankaccount()->getId():0):0])]
	 									 ],
	 				 'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
	 				 'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
	 															["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
	 				 /*'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>$tab=='data'?true:false, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
	 										["name" => "paymentroll", "active"=>($tab=='paymentroll' && $id)?true:false, "caption"=>"Nóminas"]
	 									 ]*/
	  ));
	 }


	 /**
	  * @Route("/{_locale}/company/bankaccount/data/{id}/{action}/{identity}", name="dataCompanyBankAccounts", defaults={"id"=0, "action"="read", "identity"=0})
	  */
	  public function dataCompanyBankAccounts($id, $action, $identity, Request $request)
	  {
	  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	  $this->denyAccessUnlessGranted('ROLE_ADMIN');
	  $template=dirname(__FILE__)."/../../ERP/Forms/BankAccounts.json";
	  $utils = new GlobaleFormUtils();
	  $utilsObj=new ERPBankAccountsUtils();
	  if($identity==0) $identity=$request->query->get('entity');
	  $bankaccountRepository=$this->getDoctrine()->getRepository(ERPBankAccounts::class);
	  $obj=new ERPBankAccounts();
	  if($id==0){
	 	if($identity==0 ) $identity=$request->query->get('entity');
	 	if($identity==0 || $identity==null) $identity=$request->request->get('id-parent',0);
	 }else $obj = $bankaccountRepository->find($id);
	  $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), ];
	  $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
	 												method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null, ["identity"=>$identity],
													[],['@ERP/bankaccountSEPA.html.twig']
												);

	 	//-----------------   CLOUD ----------------------
	 	$utilsCloud = new CloudFilesUtils();
	 	$path="bankaccounts";
	 	$templateLists=["id"=>$path,"list"=>[$utilsCloud->formatList($this->getUser(),$path,$id)],"path"=>$this->generateUrl("cloudUpload",["id"=>$id, "path"=>$path])];
	 	//------------------------------------------------

		$return=$utils->make($id, ERPBankAccounts::class, $action, "formIdentities", "full", "@Globale/form.html.twig", 'none', null, ["filesERPBankAccounts"=>["template"=>"@Cloud/genericlistfiles.html.twig", "vars"=>["cloudConstructor"=>$templateLists]]]);
		if(is_a($return,'App\Modules\Globale\Utils\GlobaleJsonResponse')){
			$returnArray=json_decode($return->getData(), true);
			if($returnArray["result"]==true){
				$repository=$this->getDoctrine()->getRepository($this->class);
			 	$company = $repository->findOneBy(['id'=>$identity]);
				$bankAccount=$bankaccountRepository->find($returnArray["id"]);
				$company->setBankaccount($bankAccount);
				$this->getDoctrine()->getManager()->persist($company);
				$this->getDoctrine()->getManager()->flush();
			}
		}
		return $return;
	  //return $utils->make($id, $this->class, $action, "formIdentities", "modal");
	 }


	 /**
	  * @Route("/{_locale}/mycompany/bankaccount/data/{id}/{action}/{identity}", name="dataMyCompanyBankAccounts", defaults={"id"=0, "action"="read", "identity"=0})
	  */
	  public function dataMyCompanyBankAccounts($id, $action, $identity, Request $request)
	  {
	  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	  $this->denyAccessUnlessGranted('ROLE_ADMIN');
	  $template=dirname(__FILE__)."/../../ERP/Forms/MyCompanyBankAccounts.json";
	  $utils = new GlobaleFormUtils();
	  $utilsObj=new ERPBankAccountsUtils();
	  if($identity==0) $identity=$request->query->get('entity');
	  $bankaccountRepository=$this->getDoctrine()->getRepository(ERPBankAccounts::class);
	  $obj=new ERPBankAccounts();
	  if($id==0){
	  if($identity==0 ) $identity=$request->query->get('entity');
	  if($identity==0 || $identity==null) $identity=$request->request->get('id-parent',0);
	 }else $obj = $bankaccountRepository->find($id);
	  $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), ];
	  $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
	 											 method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null, ["identity"=>$identity],
	 											 [],['@ERP/bankaccountSEPA.html.twig']
	 										 );

	   return $utils->make($id, ERPBankAccounts::class, $action, "formIdentities", "full", "@Globale/form.html.twig", 'none', null);


	  //return $utils->make($id, $this->class, $action, "formIdentities", "modal");
	 }





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
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

		$utils = new GlobaleCompaniesUtils();
		$templateLists[]=$utils->formatList($this->getUser());
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
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
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Companies.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class);
		return new JsonResponse($return);
	}


		/**
		 * @Route("/api/global/companies/{id}/get", name="getCompany")
		 */
		public function getCompany($id){
			$company = $this->getDoctrine()->getRepository($this->class)->findById($id);
			if (!$company) {
        throw $this->createNotFoundException('No company found for id '.$id );
			}

			return new JsonResponse();
			return new JsonResponse($company->encodeJson());
		}


		/**
	   * @Route("/{_locale}/company/data/{id}/{action}", name="dataCompany", defaults={"id"=0, "action"="read"})
	   */
	   public function data($id, $action, Request $request){
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    $this->denyAccessUnlessGranted('ROLE_ADMIN');
	    $template=dirname(__FILE__)."/../Forms/Companies.json";
	    $utils = new GlobaleFormUtils();
	    $utilsObj=new $this->utilsClass();
			$obj = new $this->class();
			if($id==0){
			 $defaultCountry=$this->getDoctrine()->getRepository(GlobaleCountries::class);
	 		 $default=$defaultCountry->findOneBy(['name'=>"España"]);
	 		 $obj->setCountry($default);
			 $defaultCurrency=$this->getDoctrine()->getRepository(GlobaleCurrencies::class);
	 		 $default=$defaultCurrency->findOneBy(['name'=>"Euro"]);
	 		 $obj->setCurrency($default);
			}

	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
	    $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	    return $utils->make($id, $this->class, $action, "formCompany", "full", "@Globale/form.html.twig", 'formCompany', $this->utilsClass);
	  }

		/**
	   * @Route("/{_locale}/mycompany/data/{action}", name="dataCompanyAdmin", defaults={"action"="read"})
	   */
	   public function dataCompanyAdmin($action, Request $request){
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    $this->denyAccessUnlessGranted('ROLE_ADMIN');
	    $template=dirname(__FILE__)."/../Forms/CompaniesAdmin.json";
			$id=$this->getUser()->getCompany()->getId();
	    $utils = new GlobaleFormUtils();
	    $utilsObj=new $this->utilsClass();
			$obj = new $this->class();
			if($id==0){
			 $defaultCountry=$this->getDoctrine()->getRepository(GlobaleCountries::class);
	 		 $default=$defaultCountry->findOneBy(['name'=>"España"]);
	 		 $obj->setCountry($default);
			 $defaultCurrency=$this->getDoctrine()->getRepository(GlobaleCurrencies::class);
	 		 $default=$defaultCurrency->findOneBy(['name'=>"Euro"]);
	 		 $obj->setCurrency($default);
			}

	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
	    $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	    return $utils->make($id, $this->class, $action, "formCompany", "full", "@Globale/form.html.twig", 'formCompany', $this->utilsClass);
	  }


		/**
		* @Route("/api/globale/{id}/sepa/{type}/print", name="printSEPA")
		*/
		public function print($id, $type, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_GLOBAL');
			$companyRepository=$this->getDoctrine()->getRepository($this->class);
			$company = $companyRepository->findOneBy(["id"=>$id]);
			if($company){
				$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser(), "id"=>$id, "company"=>$company, "type"=>$type];
				$reports = new GlobaleSEPAReports();
				$pdf=$reports->create($params);
				return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
			}else return new Response('');
			//return new Response('');
		}


	/**
	* @Route("/{_locale}/admin/global/companies/{id}/disable", name="disableCompany")
	*/
	public function disable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

	/**
	* @Route("/{_locale}/admin/global/companies/{id}/enable", name="enableCompany")
	*/
	public function enable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

	/**
	* @Route("/{_locale}/admin/global/companies/{id}/delete", name="deleteCompany")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
