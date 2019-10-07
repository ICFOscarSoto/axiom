<?php

namespace App\Modules\Globale\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/")
 */
class GlobaleDefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(RouterInterface $router,Request $request): Response
    {
		$this->router = $router;
        return new RedirectResponse($this->router->generate('dashboard'));
    }

	 /**
     * @Route("/{_locale}/admin", name="indexAdmin")
     */
    public function indexAdmin(RouterInterface $router,Request $request): Response
    {
		$this->router = $router;
        return new RedirectResponse($this->router->generate('dashboard'));
    }

		 /**
     * @Route("/{_locale}/admin/", name="indexAdmin2")
     */
    public function indexAdmin2(RouterInterface $router,Request $request): Response
    {
		$this->router = $router;
        return new RedirectResponse($this->router->generate('dashboard'));
    }

    /**
     * @Route("/{_locale}/generic/{module}/{name}/index", name="genericindex")
     */
    public function genericindex($module, $name, RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $class="\App\Modules\\".$module."\Entity\\".$module.$name;
    $classUtils="\App\Modules\\".$module."\Utils\\".$module.$name.'Utils';
		$utils = new $classUtils();
		$templateLists[]=$utils->formatList($this->getUser());
		$formUtils=new GlobaleFormUtils();
		$formUtils->initialize($this->getUser(), new $class(), dirname(__FILE__)."/../../".$module."/Forms/".$name.".json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils->formatForm($name, true, null, $class,null,["module"=>$module, "name"=>$name]);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => $name.'Controller',
				'interfaceName' => $this->get('translator')->trans($name),
				'optionSelected' => $request->attributes->get('_route'),
        'optionSelectedParams' => ["module"=>$module, "name"=>$name],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route'), $module, $name),
				'userData' => $userdata,
				'lists' => $templateLists,
        'forms' => $templateForms,
        'include_header' => [["type"=>"css", "path"=>"/js/jvectormap/jquery-jvectormap-1.2.2.css"],
                             ["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
                             ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
        'include_footer' => [["type"=>"css", "path"=>"/js/ol/ol.css"],
                             ["type"=>"js",  "path"=>"/js/ol/ol.js"],
                             ["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                             ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
                             ["type"=>"css", "path"=>"/css/timeline.css"]]
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
    }

   /**
    * @Route("/{_locale}/{module}/{name}/generic/data/{id}/{action}/{type}", name="genericdata", defaults={"id"=0, "action"="read", "type"="modal"})
    */
    public function data($id, $module, $name, $action, $type, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $template=dirname(__FILE__)."/../../".$module."/Forms/".$name.".json";
      $class="\App\Modules\\".$module."\Entity\\".$module.$name;
      $utils = new GlobaleFormUtils();
      $classUtils="\App\Modules\\".$module."\Utils\\".$module.$name.'Utils';
      if(class_exists($classUtils)){
        $utilsObj=new $classUtils();
      }else $utilsObj=new $class(); // define the main class to ensure that a valid object is created and not has getIncludedForm and getExcludedForm
      $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
      $utils->initialize($this->getUser(), new $class(), $template, $request, $this, $this->getDoctrine(),
        method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null,["module"=>$module, "name"=>$name]
      );
      return $utils->make($id, $class, $action, "form".$name, $type);
   }

   /**
    * @Route("/{_locale}/{widget}/widgetgeneric/data/{id}/{action}", name="widgetdata", defaults={"id"=0, "action"="read"})
    */
    public function widgetdata($id, $widget, $action, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $template=dirname(__FILE__)."/../../../Widgets/Forms/".$widget.".json";
      $class="\App\Widgets\Entity\Widgets".$widget;
      $utils = new GlobaleFormUtils();
      $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
      $utils->initialize($this->getUser(), new $class(), $template, $request, $this, $this->getDoctrine(),
                         ["userwidget"],[],null,["widget"=>$widget]);
      return $utils->make($id, $class, $action, "form".$widget, "modal");

   }

   /**
    * @Route("/{_locale}/{module}/{name}/generic/datatab/{id}/{action}/{idparent}/{type}", name="genericdatatab", defaults={"id"=0, "idparent"="0", "type"="modal", "action"="read"})
    */
    public function genericdatatab($id, $idparent, $module, $name, $type, $action, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $template=dirname(__FILE__)."/../../".$module."/Forms/".$name.".json";
      $class="\App\Modules\\".$module."\Entity\\".$module.$name;
      $utils = new GlobaleFormUtils();
      $classUtils="\App\Modules\\".$module."\Utils\\".$module.$name.'Utils';
      $classRepository=$this->getDoctrine()->getRepository($class);
      if(class_exists($classUtils)){
        $utilsObj=new $classUtils();
      }else $utilsObj=new $class(); // define the main class to ensure that a valid object is created and not has getIncludedForm and getExcludedForm
      $parentRepository=$this->getDoctrine()->getRepository($utilsObj->parentClass);
      $obj=new $class();
      if($id==0){
        if($idparent==0 ) $idparent=$request->query->get('idparent');
        if($idparent==0 || $idparent==null) $idparent=$request->request->get('id-parent',0);
        $parent = $parentRepository->find($idparent);
      }	else $obj = $classRepository->find($id);
      $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$id==0?$parent:$obj->{"get".ucfirst($utilsObj->parentField)}()];
      $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
        method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[$utilsObj->parentField],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],null,["module"=>$module, "name"=>$name]);
      if(isset($parent)) $utils->values([$utilsObj->parentField=>$parent]);
      return $utils->make($id, $class, $action, "form".$name, $type);
   }

     /**
      * @Route("/api/{module}/{name}/generic/list/{parent}/{field}/{parentModule}/{parentName}", name="genericlist", defaults={"parent"=0, "field"=null, "parentModule"="", "parentName"=""})
      */
     public function list($module, $name, $parent, $field, $parentModule, $parentName, RouterInterface $router,Request $request){
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       $user = $this->getUser();
       $locale = $request->getLocale();
       $this->router = $router;
       $manager = $this->getDoctrine()->getManager();
       $class="\App\Modules\\".$module."\Entity\\".$module.$name;
       $repository = $manager->getRepository($class);
       $filter=[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]];
       if($parent!=0 && $field!=null){
         $classParent="\App\Modules\\".$parentModule."\Entity\\".$parentModule.$parentName;
         $repositoryParent = $manager->getRepository($classParent);
         $parentObj=$repositoryParent->findOneBy(["id"=>$parent]);
         $filter[]=["type"=>"and", "column"=>$field, "value"=>$parentObj];
       }
       $listUtils=new GlobaleListUtils();
       $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../../".$module."/Lists/".$name.".json"),true);
       if(property_exists($class, "user") && !in_array("ROLE_GLOBAL", $user->getRoles()) && !in_array("ROLE_SUPERADMIN", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, $filter);
        else if(property_exists($class, "company")) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, $filter);
          else $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, count($filter)>1?[$filter[1]]:[]);
       return new JsonResponse($return);
     }


     /**
      * @Route("/api/{module}/{name}/generic/tablist/{id}", name="generictablist", defaults={"id"=0})
      */
     public function tablist($module, $name, $id, RouterInterface $router,Request $request){
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       //$this->denyAccessUnlessGranted('ROLE_ADMIN');
       $userdata=$this->getUser()->getTemplateData();
       $locale = $request->getLocale();
       $this->router = $router;
       $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
       $class="\App\Modules\\".$module."\Entity\\".$module.$name;
       $classUtils="\App\Modules\\".$module."\Utils\\".$module.$name.'Utils';
       $utils = new $classUtils();
       //TODO Check Errors change on 2019-10-04
       //$templateLists=$utils->formatList($this->getUser());
       $templateLists=$utils->formatList($this->getUser(), $id);
       $formUtils=new GlobaleFormUtils();
       $formUtils->initialize($this->getUser(), new $class(), dirname(__FILE__)."/../../".$module."/Forms/".$name.".json", $request, $this, $this->getDoctrine());
       $templateForms[]=$formUtils->formatForm($name, true, null, $class,null,["module"=>$module, "name"=>$name]);
       if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
         return $this->render('@Globale/list.html.twig', [
           'listConstructor' => $templateLists,
           'forms' => $templateForms,
           'id' => $id
           ]);
       }
       return new RedirectResponse($this->router->generate('app_login'));
     }


     /**
      * @Route("/{_locale}/{module}/{name}/generic/export", name="genericexport")
      */
      public function export($module, $name, Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $utilsExport = new GlobaleExportUtils();
        $user = $this->getUser();
        $manager = $this->getDoctrine()->getManager();
        $class="\App\Modules\\".$module."\Entity\\".$module.$name;
        $repository = $manager->getRepository($class);
        $listUtils=new GlobaleListUtils();
        $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../../".$module."/Exports/".$name.".json"),true);
        if(property_exists($class, "user") && !in_array("ROLE_GLOBAL", $user->getRoles()) && !in_array("ROLE_SUPERADMIN", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and", "column"=>"user", "value"=>$user]],[],-1);
        else if(property_exists($class, "company")) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]],[],-1);
          else $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class,[],[],-1);
        $result = $utilsExport->export($list,$listFields);
        return $result;
      }




      /**
       * @Route("/{_locale}/{module}/{name}/generic/print", name="genericprint")
       */
       public function print($module, $name, Request $request){
         $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
         $utilsPrint = new GlobalePrintUtils();
         $user = $this->getUser();
         $manager = $this->getDoctrine()->getManager();
         $class="\App\Modules\\".$module."\Entity\\".$module.$name;
         $repository = $manager->getRepository($class);
         $listUtils=new GlobaleListUtils();
         $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../../".$module."/Exports/".$name.".json"),true);
         if(property_exists($class, "user") && !in_array("ROLE_GLOBAL", $user->getRoles()) && !in_array("ROLE_SUPERADMIN", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and", "column"=>"user", "value"=>$user]],[],-1);
         else if(property_exists($class, "company")) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]],[],-1);
           else $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class,[],[],-1);
         $result = $utilsPrint->print($list,$listFields);
         return $result;
       }

     /**
     * @Route("/{_locale}/{module}/{name}/generic/{id}/disable", name="genericdisable")
     */
     public function disable($module, $name, Request $request, $id){
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $entityUtils=new GlobaleEntityUtils();
      $class="\App\Modules\\".$module."\Entity\\".$module.$name;
      $result=$entityUtils->disableObject($id, $class, $this->getDoctrine());
      return new JsonResponse(array('result' => $result));
     }
     /**
     * @Route("/{_locale}/{module}/{name}/generic/{id}/enable", name="genericenable")
     */
     public function enable($module, $name, Request $request, $id){
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $entityUtils=new GlobaleEntityUtils();
      $class="\App\Modules\\".$module."\Entity\\".$module.$name;
      $result=$entityUtils->enableObject($id, $class, $this->getDoctrine());
      return new JsonResponse(array('result' => $result));
     }
     /**
     * @Route("/{_locale}/{module}/{name}/generic/{id}/delete", name="genericdelete", defaults={"id"=0})
     */
     public function delete($module, $name, Request $request, $id){
      $this->denyAccessUnlessGranted('ROLE_ADMIN');
      $entityUtils=new GlobaleEntityUtils();
      $class="\App\Modules\\".$module."\Entity\\".$module.$name;
      if($id!=0) $result=$entityUtils->deleteObject($id, $class, $this->getDoctrine());
       else {
         $ids=$request->request->get('ids');
         $ids=explode(",",$ids);
         foreach($ids as $item){
           $result=$entityUtils->deleteObject($item, $class, $this->getDoctrine());
         }
       }
      return new JsonResponse(array('result' => $result));
     }

     /**
   	 * @Route("/api/globale/generictrigger", name="generictrigger", defaults={"id"=0})
   	 */
   	 public function generictrigger(Request $request){
   		 if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
        $id = $request->request->get("id");
        $module = $request->request->get("mod");
        $class = $request->request->get("name");
        $triggermodule = $request->request->get("modtrg");
        $triggername = $request->request->get("nametrg");
        $relationParameter = $request->request->get("prm");
   		 	$triggerRepository = $this->getDoctrine()->getRepository("\App\Modules\\".$triggermodule."\Entity\\".$triggername);
   			$repository = $this->getDoctrine()->getRepository("\App\Modules\\".$module."\Entity\\".$class);
   			$triggerObj=$triggerRepository->findOneBy(["id"=>$id, "active"=>1,"deleted"=>0, "company"=>$this->getUser()->getCompany()]);
   			$objects=$repository->findBy([$relationParameter=>$triggerObj,"active"=>1,"deleted"=>0]);
   			$return=[];
   			foreach($objects as $item){
   				$option["id"]=$item->getId();
   				$option["text"]=$item->getName();
   				$return[]=$option;
   			}
   			return new JsonResponse($return);
   	 	}else{
   			return new JsonResponse([]);
   		}
    	}
}
