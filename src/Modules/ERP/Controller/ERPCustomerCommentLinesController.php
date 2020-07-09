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
use App\Modules\ERP\Utils\ERPCustomersUtils;
use App\Modules\ERP\Utils\ERPCustomerCommentLinesUtils;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;

class ERPCustomerCommentLinesController extends Controller
{

		private $class=ERPCustomerCommentLines::class;
		private $utilsClass=ERPCustomerCommentLinesUtils::class;
    /**
   	* @Route("/api/customercommentlines/list/{customerid}/{type}", name="customercommentlineslist")
   	*/
    public function customercommentlineslist(RouterInterface $router,Request $request, $customerid, $type){
	   	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   	 $user = $this->getUser();
   	 $locale = $request->getLocale();
   	 $this->router = $router;
   	 $manager = $this->getDoctrine()->getManager();
   	 $repository = $manager->getRepository($this->class);
   	 $listUtils=new GlobaleListUtils();
   	 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerCommentLines.json"),true);

   	 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPCustomerCommentLines::class,[["type"=>"and", "column"=>"customer", "value"=>$customerid],["type"=>"and", "column"=>"type", "value"=>$type]]);
		 return new JsonResponse($return);
    }

		/**
		* @Route("/api/customercommentlinesordersdata/list/{customerid}/{type}", name="customercommentlinesordersdatalist")
		*/
		public function customercommentlineslistordersdata(RouterInterface $router,Request $request, $customerid, $type){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $user = $this->getUser();
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerCommentLinesOrdersData.json"),true);

		 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPCustomerCommentLines::class,[["type"=>"and", "column"=>"customer", "value"=>$customerid],["type"=>"and", "column"=>"type", "value"=>$type]]);
		 return new JsonResponse($return);
		}

		/**
		 * @Route("/{_locale}/customercommentlines/data/{id}/{action}/{idparent}/{type}/{type_comment}", name="dataCustomerCommentLines", defaults={"id"=0, "idparent"="0", "type"="modal", "action"="read", "type_comment"="0"})
		 */
		 public function data($id, $idparent, $type, $type_comment, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $class="\App\Modules\ERP\Entity\ERPCustomerCommentLines";
		 $utils = new GlobaleFormUtils();
		 $classUtils="\App\Modules\ERP\Utils\ERPCustomerCommentLinesUtils";
		 $template=dirname(__FILE__)."/../Forms/CustomerCommentLines.json";
		 $classRepository=$this->getDoctrine()->getRepository($class);
		 if(class_exists($classUtils)){
			 $utilsObj=new $classUtils();
		 }else $utilsObj=new $class();
		 $parentRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		 $obj=new $class();
		 if($id==0){
			 if($idparent==0 ) $idparent=$request->query->get('idparent');
			 if($idparent==0 || $idparent==null) $idparent=$request->request->get('id-parent',0);
			 $parent = $parentRepository->find($idparent);
		 }	else $obj = $classRepository->find($id);

		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];

 		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
	 		 												method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
		 if($id==0) $utils->values(["customer"=>$parent,"type"=>$type_comment]);
		 return $utils->make($id, $this->class, $action, "formCustomerCommentLines", "modal");
		}

		/**
		 * @Route("/{_locale}/customercommentlines/dataOrdersData/{id}/{action}/{idparent}/{type}/{type_comment}", name="dataCustomerCommentLinesOrdersData", defaults={"id"=0, "idparent"="0", "type"="modal", "action"="read", "type_comment"="0"})
		 */
		 public function dataOrdersData($id, $idparent, $type, $type_comment, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $class="\App\Modules\ERP\Entity\ERPCustomerCommentLines";
		 $utils = new GlobaleFormUtils();
		 $classUtils="\App\Modules\ERP\Utils\ERPCustomerCommentLinesUtils";
		 $template=dirname(__FILE__)."/../Forms/CustomerCommentLinesOrdersData.json";
		 $classRepository=$this->getDoctrine()->getRepository($class);
		 if(class_exists($classUtils)){
			 $utilsObj=new $classUtils();
		 }else $utilsObj=new $class();
		 $parentRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		 $obj=new $class();
		 if($id==0){
			 if($idparent==0 ) $idparent=$request->query->get('idparent');
			 if($idparent==0 || $idparent==null) $idparent=$request->request->get('id-parent',0);
			 $parent = $parentRepository->find($idparent);
		 }	else $obj = $classRepository->find($id);

		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];

		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
															method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
		 if($id==0) $utils->values(["customer"=>$parent,"type"=>$type_comment]);
		 dump($utils);
		 return $utils->make($id, $this->class, $action, "formCustomerCommentLinesOrdersData", "modal");
		}






}
