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
use App\Modules\ERP\Entity\ERPIncrements;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPIncrementsUtils;
use App\Modules\ERP\Utils\ERPSuppliersUtils;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPSuppliers;

class ERPIncrementsController extends Controller
{
	
		private $class=ERPIncrements::class;
		private $utilsClass=ERPIncrementsUtils::class;
		
		
		/**
     * @Route("/{_locale}/ERP/{id}/supplierincrements", name="supplierincrements")
     */
	
		public function supplierindex($id, RouterInterface $router,Request $request)
		{
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 //$this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $userdata=$this->getUser()->getTemplateData();
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		 $utils = new ERPIncrementsUtils;
		 $templateLists=$utils->formatListbyEntity($id);
		 $formUtils=new GlobaleFormUtils();
		 $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/SupplierIncrements.json", $request, $this, $this->getDoctrine());
		 $templateForms[]=$formUtils->formatForm('increments', true, $id, $this->class, "dataSupplierIncrements",["id"=>$id, "action"=>"save"]);
		 $entitiesrepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		 $entity=$entitiesrepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
		 if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			 return $this->render('@Globale/list.html.twig', [
				 'listConstructor' => $templateLists,
				 'forms' => $templateForms,
				 'entity_id' => $id
				 ]);
		 }
		 return new RedirectResponse($this->router->generate('app_login'));
		}
	  
		
		/**
		 * @Route("/{_locale}/supplierincrement/data/{id}/{action}/{identity}", name="dataSupplierIncrements", defaults={"id"=0, "action"="read", "identity"=0})
		 */
		 public function data($id, $action, $identity, Request $request)
		 {
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/SupplierIncrements.json";
		 $utils = new GlobaleFormUtils();
		 $utilsObj=new $this->utilsClass();
		 if($identity==0) $identity=$request->query->get('entity');
		 $defaultSupplier=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		 $incrementRepository=$this->getDoctrine()->getRepository(ERPIncrements::class);
		 $obj=new $this->class();
		 if($id==0){
				if($identity==0 ) $identity=$request->query->get('entity');
				if($identity==0 || $identity==null) $identity=$request->request->get('id-parent',0);
				$supplier = $defaultSupplier->find($identity);
			}
		 else $obj = $incrementRepository->find($id);
		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "supplier"=>$id==0?$supplier:$obj->getSupplier()];
		 dump($params);
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
														method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[]);
		 return $utils->make($id, $this->class, $action, "formIdentities", "modal");
		}
		
		
		/**
	   * @Route("/api/supplierincrement/{id}/list", name="supplierincrementlist")
	   */
	  public function supplierindexlist($id,RouterInterface $router,Request $request){
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    $user = $this->getUser();
			$supplierRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
			$supplier = $supplierRepository->find($id);
	    $locale = $request->getLocale();
	    $this->router = $router;
	    $manager = $this->getDoctrine()->getManager();
	    $repository = $manager->getRepository(ERPIncrements::class);
	    $listUtils=new GlobaleListUtils();
	    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/IncrementsSupplier.json"),true);
	    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPIncrements::class,[["column"=>"supplier", "value"=>$supplier]]);
	    return new JsonResponse($return);
	
	  }
		
		/**
		* @Route("/{_locale}/admin/global/supplierincrement/{id}/disable", name="disableSupplierIncrement")
		*/
	 public function disable($id)
		 {
		 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
		 $entityUtils=new GlobaleEntityUtils();
		 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		 return new JsonResponse(array('result' => $result));
	 }
	 /**
	 * @Route("/{_locale}/admin/global/supplierincrement/{id}/enable", name="enableSupplierIncrement")
	 */
	 public function enable($id)
		 {
		 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
		 $entityUtils=new GlobaleEntityUtils();
		 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		 return new JsonResponse(array('result' => $result));
	 }
	 /**
	 * @Route("/{_locale}/admin/global/supplierincrement/{id}/delete", name="deleteSupplierIncrement")
	 */
	 public function delete($id){
		 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
		 $entityUtils=new GlobaleEntityUtils();
		 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		 return new JsonResponse(array('result' => $result));
	 }

		
		/**
		* @Route("/api/globale/suppliercategoriestrigger", name="suppliercategoriestrigger", defaults={"id"=0})
		*/
		public function suppliercategoriestrigger(Request $request){
			if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
					 $id = $request->request->get("id");
					 $referencesRepository = $this->getDoctrine()->getRepository(ERPReferences::class);
					 $products_ids=$referencesRepository->ProductsBySupplier($id);
					//dump($products_ids);
					 foreach($products_ids as $product_id){
						 $productsRepository = $this->getDoctrine()->getRepository(ERPProducts::class);
						 $productObj=$productsRepository->findOneBy(["id"=>$product_id]);
						 $categoria=$productObj->getCategory();
						 $arrayCat[]=$categoria;
			 	 	}
			 
			 $return=[];
			 
			// dump($arrayCat);
			 foreach($arrayCat as $category){
				 
				 if($category->getParentid()!=NULL) $category=$category->getParentid();
				 while($category->getParentid()!=NULL)
				 {
						$arrayCat[]=$category;
						$category=$category->getParentid();
				 }
				 $arrayCat[]=$category;
				 
			 }
			// $array_total= array_values(array_unique($arrayCat));
			$aux=[];
			 foreach($arrayCat as $category){
			   if(in_array($category,$aux)==false)
				 {
					 	$option["id"]=$category->getId();
					 	$option["text"]=$category->getName();
					 	$return[]=$option;
					 	array_push($aux,$category);
					}
		 		
				}
	 				return new JsonResponse($return);
		 	}
		 
			 else{
				 return new JsonResponse([]);
			 }
		 
		 }
		 
	
}
