<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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
use App\Modules\Globale\Entity\GlobaleDiskUsages;
use \App\Helpers\HelperFiles;
use App\Modules\Security\Utils\SecurityUtils;
//use App\Modules\Globale\UtilsEntityUtils;
//use App\Modules\Form\Controller\FormController;

class GlobaleCompaniesController extends Controller
{
	 private $module='Globale';
	 private $class=GlobaleCompanies::class;
	 private $utilsClass=GlobaleCompaniesUtils::class;



	 /**
	  * @Route("/{_locale}/globale/company/form/{id}", name="formCompany", defaults={"id"=0})
	  */
	  public function formCompany($id, Request $request){
	 	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

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
	 					'menuOptions' =>  $menurepository->formatOptions($userdata),
	 					'breadcrumb' => $breadcrumb,
	 					'userData' => $userdata,
	 					'id' => $id,
	 					'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
	 					'tabs' => [["name" => "data", "caption"=>"Datos empresa", "icon"=>"entypo-book-open","active"=>true, "route"=>$this->generateUrl("dataCompany",["id"=>$id])],
	 										 ["name" => "bank", "icon"=>"fa fa-headphones", "caption"=>"Datos bancarios", "route"=>$this->generateUrl("dataCompanyBankAccounts",["identity"=>$id,"id"=>$obj?($obj->getBankaccount()?$obj->getBankaccount()->getId():0):0])],
											 ["name" => "modules", "caption"=>"Módulos", "icon"=>"fa fa-users", "route"=>$this->generateUrl("generictablist",["module"=>"Globale", "name"=>"CompaniesModules", "id"=>$id])],
											 ["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"companies"])],
											 ["name" => "diskusage", "icon"=>"fa fa-database", "caption"=>"Uso disco", "route"=>$this->generateUrl("diskusage",["id"=>$id])]
	 										],
	 					'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
	 					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
	 															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
																 ["type"=>"js",  "path"=>"/js/raphael-min.js"],
																 ["type"=>"js",  "path"=>"/js/morris.min.js"],
																 ["type"=>"js",  "path"=>"/js/chartjs/Chart.min.js"]
																]
	 					/*'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>$tab=='data'?true:false, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
	 										 ["name" => "paymentroll", "active"=>($tab=='paymentroll' && $id)?true:false, "caption"=>"Nóminas"]
	 										]*/
	 	));
	 }

	 /**
	  * @Route("/{_locale}/globale/admin/company/{id}/discusage", name="diskusage")
	  */
	  public function discusage($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		  $this->denyAccessUnlessGranted('ROLE_GLOBAL');
			$userdata=$this->getUser()->getTemplateData();

			$filesHelper=new HelperFiles();

			$formUtils=new GlobaleFormUtils();
			$companiesRepository = $this->getDoctrine()->getRepository(GlobaleCompanies::class);
	    $diskusagesRepository = $this->getDoctrine()->getRepository(GlobaleDiskUsages::class);
			$company=$companiesRepository->find($id);

			$diskusage=$company->getDiskUsages();
			$data["roles"]=$this->getUser()->getRoles();
			$data["diskusage"]["space"]=$filesHelper->formatBytes($diskusage[0]->getDiskspace());
			$data["diskusage"]["free"]=$filesHelper->formatBytes($diskusage[0]->getDiskspace()-$diskusage[0]->getDiskusage());
			$data["diskusage"]["free_perc"]=round($diskusage[0]->getDiskusage()*100/$diskusage[0]->getDiskspace(),1);
			$data["diskusage"]["distribution"]=json_decode($diskusage[0]->getDistribution(),true);

			$diskusage=$diskusagesRepository->findOneBy(["companyown"=>$company]);
			$formUtils->initialize($this->getUser(), new GlobaleDiskUsages(), dirname(__FILE__)."/../Forms/DiskUsages.json", $request, $this, $this->getDoctrine(),["companyown"]);
			$templateForm=$formUtils->formatForm('formDiskusages', true, $diskusage->getId(), GlobaleDiskUsages::class,'dataDiskusages');
			$templateForm["type"]="modal"; //Hide Save buttons
			return $this->render('@Globale/diskusage.html.twig', array(
				'userData' => $data,
				'id' => $id,
				'form' => $templateForm
			));
		}

		/**
		 * @Route("/{_locale}/diskusages/data/{id}/{action}", name="dataDiskusages", defaults={"id"=0, "action"="read"})
		 */
		 public function dataDiskusages($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
		 $template=dirname(__FILE__)."/../Forms/DiskUsages.json";
		 $utils = new GlobaleFormUtils();
		 //$diskusagesRepository = $this->getDoctrine()->getRepository(GlobaleDiskUsages::class);
		 //$diskusage=$diskusagesRepository->find($id);
		 $utils->initialize($this->getUser(), new GlobaleDiskUsages(), $template, $request, $this, $this->getDoctrine(),["companyown"]);
		 return $utils->make($id, GlobaleDiskUsages::class, $action, "formDiskusages", "full");
		}


	 /**
	  * @Route("/{_locale}/globale/admin/mycompany", name="mycompany")
	  */
	  public function mycompany(Request $request){
	  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
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
	 				 'menuOptions' =>  $menurepository->formatOptions($userdata),
	 				 'breadcrumb' => $breadcrumb,
	 				 'userData' => $userdata,
	 				 'id' => $id,
	 				 'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
	 				 'tabs' => [["name" => "data", "caption"=>"Datos empresa", "icon"=>"entypo-book-open","active"=>true, "route"=>$this->generateUrl("dataCompanyAdmin",["id"=>$id])],
					 						["name" => "bank", "icon"=>"fa fa-headphones", "caption"=>"Datos bancarios", "route"=>$this->generateUrl("dataMyCompanyBankAccounts",["identity"=>$id,"id"=>$obj?($obj->getBankaccount()?$obj->getBankaccount()->getId():0):0])],
											["name" => "bank", "icon"=>"fa fa-clocks", "caption"=>"Cierre Jornada", "route"=>$this->generateUrl("generictablist",["module"=>"HR", "name"=>"AutoCloseClocks", "id"=>$id])]
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
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
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
				'menuOptions' =>  $menurepository->formatOptions($userdata),
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
	    $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),["agent", "bankaccount"]);
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

	/**
	* @Route("/{_locale}/global/companies/options/connectas", name="optionsConnectas")
	*/
	public function optionsConnectas(Request $request){
		if ($this->get('security.authorization_checker')->isGranted('ROLE_GLOBAL')) {
			$repository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
			$objs = $repository->findBy(['deleted'=>0]);
			$options=[];
			$session = new Session();
      $connectas=$session->get('as_company',$this->getUser()->getCompany());
			foreach($objs as $obj){

				$options[]=["id"=>$obj->getId(), "text"=>$obj->getName(), "selected"=>$connectas->getId()==$obj->getId()?true:false];
			}
			//return new JsonResponse(["results"=>$options,"pagination"=>["more"=>true]]);
			return new JsonResponse($options);
	  }else return new JsonResponse(["results"=>[],"pagination"=>["more"=>true]]);

	}

	/**
	* @Route("/api/global/shop/module", name="shopModule")
	*/
	public function shopModule(Request $request){
			$data = json_decode($request->getContent(), true);

			//Generate signature
			$signature = base64_encode(hash_hmac('sha256', $request->getContent(), 'sT/hta:RoaR~9<SM|F|*{S;22b,2@~r7n$RUJV-di6l|Tb[6:y', true));
			$headers = $request->headers->all();
			if(!isset($headers["x-wc-webhook-signature"])) return new JsonResponse(["result"=>-1]);
			if($signature!=$headers["x-wc-webhook-signature"][0]) return new JsonResponse(["result"=>-1]);

			$repository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
			$countriesRepository=$this->getDoctrine()->getRepository(GlobaleCountries::class);
			$currenciesRepository=$this->getDoctrine()->getRepository(GlobaleCurrencies::class);
			$companyCif=null;
			$companyDomain=null;
			$companyName=null;
			$orderId=$data["id"];
			//Search company's domain
			if(isset($data["meta_data"])){
				foreach($data["meta_data"] as $item){
					if($item["key"]=="billing_cif") $companyCif=$item["value"];
						elseif ($item["key"]=="billing_domain") $companyDomain=$item["value"];
							elseif ($item["key"]=="billing_company") $companyName=$item["value"];
				}
			}
			//if($companyDomain==null) return new JsonResponse(["result"=>0]);
			$companyDomain=($companyDomain==null || $companyDomain=="")?preg_replace('/[^a-zA-Z0-9]/', '', $companyName):$companyDomain;
			$company=$repository->findOneBy(["domain"=>$companyDomain]);
			if($company){ //Company already exists
				//TODO: At the moment do nothing in this point. In the future activate or deactivate modules to the company
			}else{ //New company
				$company=new GlobaleCompanies();
				$company->setVat($companyCif);
				$company->setDomain($companyDomain);
				$company->setName($companyName);
				$company->setSocialname($companyName);
				$company->setAddress($data["billing"]["address_1"]);
				$company->setCity($data["billing"]["city"]);
				//$company->setState();
				$company->setPostcode($data["billing"]["postcode"]);
				$company->setPhone($data["billing"]["phone"]);
				$country=$countriesRepository->findOneBy(["name"=>"España"]);
				$company->setCountry($country); //TODO: Detect de country, at the moment only Spain
				$currency=$currenciesRepository->findOneBy(["name"=>"Euro"]);
				$company->setCurrency($currency); //TODO: Detect de currency, at the moment only EUR
				$company->setDateupd(new \DateTime());
				$company->setDateadd(new \DateTime());
				$company->setActive(1);
				$company->setDeleted(0);
				$company->preProccess($this, $this->getDoctrine(), null);
				$this->getDoctrine()->getManager()->persist($company);
				$this->getDoctrine()->getManager()->flush();
				$company->postProccess($this->get('kernel'), $this->getDoctrine(), null);
			}
			//TODO: Call api method to change state of the order
			//Change order status to completed
			$ch = curl_init("https://www.aplicode.com/wp-json/wc/v3/orders/".$orderId);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, 'ck_8648d848ad674ea24154a1b446e19780086bd79e' . ":" . 'cs_2a3ed8eeb5bd1c55c9f9cf9b59f9877c04f47c37');
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["status"=>"completed"]));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$return = curl_exec($ch);
			curl_close($ch);

			return new JsonResponse(["result"=>$return]);
			//return new JsonResponse($request->headers->all());
			//$request->headers->all();
	}

}
