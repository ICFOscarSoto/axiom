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
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPWebProductsUtils;

class ERPWebProductsController extends Controller
{
	private $class=ERPWebProducts::class;
	private $utilsClass=ERPWebProductsUtils::class;
    /**
     * @Route("/{_locale}/admin/global/webproducts", name="webproducts")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new $this->utilsClass();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/WebProducts.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('webproducts', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'webproductsController',
  				'interfaceName' => 'Productos web',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists,
	        'forms' => $templateForms
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/webproducts/data/{id}/{action}", name="dataWebProducts", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/WebProducts.json";
		 $params=["doctrine"=>$this->getDoctrine(),"id"=>$id];
		 $utils = new GlobaleFormUtils();
		 //$utilsObj=new $this->utilsClass();
		 //,method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),['product']);
		 return $utils->make($id, $this->class, $action, "formWebProducts", "full", "@Globale/form.html.twig", 'formProducts', $this->utilsClass);

		}


		/**
		 * @Route("/{_locale}/webproducts/data/{action}", name="dataNewWebProducts", defaults={"id"=0, "action"="read"})
		 */
		 public function dataNew($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/WebProducts.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make(0, $this->class, $action, "formWebProducts", "full", "@Globale/form.html.twig", 'formProducts', $this->utilsClass);

		}

    /**
    * @Route("/api/global/webproduct/{id}/get", name="getWebProduct")
    */
    public function getWebProduct($id){
      $webproduct = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$webproduct) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($webproduct->encodeJson());
    }

  /**
   * @Route("/api/webproduct/list", name="webproductlist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProducts.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, BankAccounts::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/webproduct/{id}/disable", name="disableWebProduct")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/webproduct/{id}/enable", name="enableWebProduct")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/webproduct/{id}/delete", name="deleteWebProduct")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
