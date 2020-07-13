<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSuppliersUtils;
use App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPSupplierCommentLines;

class ERPSupplierCommentLinesController extends Controller
{

		private $class=ERPSupplierCommentLines::class;
		private $utilsClass=ERPSupplierCommentLinesUtils::class;


    /**
   	* @Route("/api/suppliercommentlines/list/{supplierid}/{type}", name="suppliercommentlineslist")
   	*/
    public function suppliercommentlineslist(RouterInterface $router,Request $request, $supplierid, $type){
   	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   	 $user = $this->getUser();
   	 $locale = $request->getLocale();
   	 $this->router = $router;
   	 $manager = $this->getDoctrine()->getManager();
   	 $repository = $manager->getRepository($this->class);
   	 $listUtils=new GlobaleListUtils();
   	 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLines.json"),true);
   	 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSupplierCommentLines::class,[["type"=>"and", "column"=>"supplier", "value"=>$supplierid],["type"=>"and", "column"=>"type", "value"=>$type]]);
   	 return new JsonResponse($return);
    }


		/**
		* @Route("/api/suppliercommentlinesordersdata/list/{supplierid}/{type}", name="suppliercommentlinesordersdatalist")
		*/
		public function suppliercommentlinesordersdatalist(RouterInterface $router,Request $request, $supplierid, $type){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $user = $this->getUser();
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersData.json"),true);
		 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSupplierCommentLines::class,[["type"=>"and", "column"=>"supplier", "value"=>$supplierid],["type"=>"and", "column"=>"type", "value"=>$type]]);
		 return new JsonResponse($return);
		}

		/**
		* @Route("/api/suppliercommentlinesordersdatarappel/list/{supplierid}/{type}", name="suppliercommentlinesordersdatarappellist")
		*/
		public function suppliercommentlinesordersdatarappellist(RouterInterface $router,Request $request, $supplierid, $type){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $user = $this->getUser();
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesOrdersDataRappel.json"),true);
		 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSupplierCommentLines::class,[["type"=>"and", "column"=>"supplier", "value"=>$supplierid],["type"=>"and", "column"=>"type", "value"=>$type]]);
		 return new JsonResponse($return);
		}


		/**
		* @Route("/api/suppliercommentlinesincidents/list/{supplierid}/{type}", name="suppliercommentlinesincidentslist")
		*/
		public function suppliercommentlinesincidentslist(RouterInterface $router,Request $request, $supplierid, $type){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $user = $this->getUser();
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLinesIncidents.json"),true);
		 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSupplierCommentLines::class,[["type"=>"and", "column"=>"supplier", "value"=>$supplierid],["type"=>"and", "column"=>"type", "value"=>$type]]);
		 return new JsonResponse($return);
		}


		/**
		 * @Route("/{_locale}/suppliercommentlines/data/{id}/{action}/{idparent}/{type_comment}", name="dataSupplierCommentLines", defaults={"id"=0, "idparent"="0", "action"="read", "type_comment"="0"})
		 */
		 public function data($id, $idparent, $type_comment, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $class="\App\Modules\ERP\Entity\ERPSupplierCommentLines";
		 $utils = new GlobaleFormUtils();
		 $classUtils="\App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils";
		 $template=dirname(__FILE__)."/../Forms/SupplierCommentLines.json";
		 $classRepository=$this->getDoctrine()->getRepository($class);
		 if(class_exists($classUtils)){
			 $utilsObj=new $classUtils();
		 }else $utilsObj=new $class();
		 $parentRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		 $obj=new $class();
		 if($id==0){
			 if($idparent==0 ) $idparent=$request->query->get('idparent');
			 if($idparent==0 || $idparent==null) $idparent=$request->request->get('id-parent',0);
			 $parent = $parentRepository->find($idparent);
		 }	else $obj = $classRepository->find($id);

		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];

 		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
	 		 												method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null,["idparent"=>$idparent,"action"=>$action,"type_comment"=>$type_comment]);
		 if($id==0) $utils->values(["supplier"=>$parent,"type"=>$type_comment]);
		 return $utils->make($id, $this->class, $action, "SupplierCommentLines", "modal");
		}



		/**
		 * @Route("/{_locale}/suppliercommentlines/dataOrdersData/{id}/{action}/{idparent}/{type_comment}", name="dataSupplierCommentLinesOrdersData", defaults={"id"=0, "idparent"="0", "action"="read", "type_comment"="1"})
		 */
		 public function dataOrdersData($id, $idparent, $type_comment, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $class="\App\Modules\ERP\Entity\ERPSupplierCommentLines";
		 $utils = new GlobaleFormUtils();
		 $classUtils="\App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils";
		 $template=dirname(__FILE__)."/../Forms/SupplierCommentLinesOrdersData.json";
		 $classRepository=$this->getDoctrine()->getRepository($class);
		 if(class_exists($classUtils)){
			 $utilsObj=new $classUtils();
		 }else $utilsObj=new $class();
		 $parentRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		 $obj=new $class();
		 if($id==0){
			 if($idparent==0 ) $idparent=$request->query->get('idparent');
			 if($idparent==0 || $idparent==null) $idparent=$request->request->get('id-parent',0);
			 $parent = $parentRepository->find($idparent);
		 }	else $obj = $classRepository->find($id);

		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];

 		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
	 		 												method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null,["idparent"=>$idparent,"action"=>$action,"type_comment"=>$type_comment]);
		 if($id==0) $utils->values(["supplier"=>$parent,"type"=>$type_comment]);
		 return $utils->make($id, $this->class, $action, "SupplierCommentLinesOrdersData", "modal");
		}



		/**
		 * @Route("/{_locale}/suppliercommentlines/dataOrdersDataRappel/{id}/{action}/{idparent}/{type_comment}", name="dataSupplierCommentLinesOrdersDataRappel", defaults={"id"=0, "idparent"="0", "action"="read", "type_comment"="3"})
		 */
		 public function dataOrdersDataRappel($id, $idparent, $type_comment, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $class="\App\Modules\ERP\Entity\ERPSupplierCommentLines";
		 $utils = new GlobaleFormUtils();
		 $classUtils="\App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils";
		 $template=dirname(__FILE__)."/../Forms/SupplierCommentLinesOrdersDataRappel.json";
		 $classRepository=$this->getDoctrine()->getRepository($class);
		 if(class_exists($classUtils)){
			 $utilsObj=new $classUtils();
		 }else $utilsObj=new $class();
		 $parentRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		 $obj=new $class();
		 if($id==0){
			 if($idparent==0 ) $idparent=$request->query->get('idparent');
			 if($idparent==0 || $idparent==null) $idparent=$request->request->get('id-parent',0);
			 $parent = $parentRepository->find($idparent);
		 }	else $obj = $classRepository->find($id);

		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];

		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
															method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null,["idparent"=>$idparent,"action"=>$action,"type_comment"=>$type_comment]);
		 if($id==0) $utils->values(["supplier"=>$parent,"type"=>$type_comment]);
		 return $utils->make($id, $this->class, $action, "SupplierCommentLinesOrdersDataRappel", "modal");
		}







/**
	 		* @Route("/{_locale}/ERP/{id}/supplierincidents", name="supplierincidents")
*/

public function supplierincidents($id, RouterInterface $router,Request $request)
{
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
			 $supplier=$suppliersRepository->findOneBy(["id"=>$id]);
			 $suppliercommentlinesRepository=$this->getDoctrine()->getRepository(ERPSupplierCommentLines::class);

			 $suppliercommentlines=$suppliercommentlinesRepository->findOneBy(["supplier"=>$id,"type"=>"2"]);
		   if($suppliercommentlines)
			 {
				 $this_id=$suppliercommentlines->getId();
					//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
					$locale = $request->getLocale();
					$this->router = $router;
					$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
					$template=dirname(__FILE__)."/../Forms/SupplierCommentLinesIncidents.json";
					$formUtils = new GlobaleFormUtils();
					$listsuppliercommentlinesincidents = new ERPSupplierCommentLinesUtils();
					$formUtils->initialize($this->getUser(), new ERPSupplierCommentLines(), $template, $request, $this, $this->getDoctrine(),$listsuppliercommentlinesincidents->getExcludedForm([]),$listsuppliercommentlinesincidents->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]));
					$listsuppliercommentlinesincidents->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]);
					$forms[]=$formUtils->formatForm('SupplierCommentLinesIncidents', true, $this_id, ERPSupplierCommentLines::class);

					return $this->render('@Globale/list.html.twig', [
						'listConstructor' => $listsuppliercommentlinesincidents->formatListbySupplierTypeIncidents(2,$id),
						'forms' => $forms,
						'id' => $this_id,
						'id-parent' => $id
						]);

				}

				else{
						 $this_id=0;
						 //$this->denyAccessUnlessGranted('ROLE_ADMIN');
						 $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
						 $locale = $request->getLocale();
						 $this->router = $router;
						 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
						 $template=dirname(__FILE__)."/../Forms/SupplierCommentLinesIncidents.json";
						 $formUtils = new GlobaleFormUtils();
						 $listsuppliercommentlinesincidents = new ERPSupplierCommentLinesUtils();
						 $formUtils->initialize($this->getUser(), new ERPSupplierCommentLines(), $template, $request, $this, $this->getDoctrine(),$listsuppliercommentlinesincidents->getExcludedForm([]),$listsuppliercommentlinesincidents->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]));
						 $listsuppliercommentlinesincidents->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$supplier]);
						// $forms[]=$formUtils->formatForm('BankAccounts', null, null, ERPBankAccounts::class);
						 return $this->render('@Globale/list.html.twig', [
							 'listConstructor' => $listsuppliercommentlinesincidents->formatListbySupplierTypeIncidents(2,$id),
							 'forms' => null,
							 'id' => $this_id,
							 'parent' => $id
							 ]);
		}
 }

 /**
	* @Route("/{_locale}/suppliercommentlines/dataincidents/{id}/{action}/{idparent}/{type_comment}", name="dataSupplierCommentLinesIncidents", defaults={"id"=0, "idparent"="0", "action"="read", "type_comment"="3"})
	*/
	public function dataSupplierCommentLinesIncidents($id, $idparent, $type_comment, $action, Request $request){
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$class="\App\Modules\ERP\Entity\ERPSupplierCommentLines";
	$utils = new GlobaleFormUtils();
	$classUtils="\App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils";
	$template=dirname(__FILE__)."/../Forms/SupplierCommentLinesIncidents.json";
	$classRepository=$this->getDoctrine()->getRepository($class);
	if(class_exists($classUtils)){
		$utilsObj=new $classUtils();
	}else $utilsObj=new $class();
	$parentRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
	$obj=new $class();
	if($id==0){
		if($idparent==0 ) $idparent=$request->query->get('idparent');
		if($idparent==0 || $idparent==null) $idparent=$request->request->get('id-parent',0);
		$parent = $parentRepository->find($idparent);
	}	else $obj = $classRepository->find($id);

	$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];

	$utils = new GlobaleFormUtils();
	$utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
													 method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null,["idparent"=>$idparent,"action"=>$action,"type_comment"=>$type_comment]);
	if($id==0) $utils->values(["supplier"=>$parent,"type"=>$type_comment]);
	return $utils->make($id, $this->class, $action, "SupplierCommentLinesIncidents", "modal");
 }



}
