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
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route'), $module, $name),
				'userData' => $userdata,
				'lists' => $templateLists,
        'forms' => $templateForms
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
      * @Route("/api/{module}/{name}/generic/list", name="genericlist")
      */
     public function list($module, $name, RouterInterface $router,Request $request){
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       $user = $this->getUser();
       $locale = $request->getLocale();
       $this->router = $router;
       $manager = $this->getDoctrine()->getManager();
       $class="\App\Modules\\".$module."\Entity\\".$module.$name;
       $repository = $manager->getRepository($class);
       $listUtils=new GlobaleListUtils();
       $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../../".$module."/Lists/".$name.".json"),true);
       if(property_exists($class, "user") && !in_array("ROLE_GLOBAL", $user->getRoles()) && !in_array("ROLE_SUPERADMIN", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles())) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and", "column"=>"user", "value"=>$user]]);
        else if(property_exists($class, "company")) $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
          else $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class);
       return new JsonResponse($return);
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
}
