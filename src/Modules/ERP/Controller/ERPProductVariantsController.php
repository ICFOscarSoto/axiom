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
use App\Modules\ERP\Entity\ERPProductVariants;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductVariantsUtils;

class ERPProductVariantsController extends Controller
{
	private $class=ERPProductVariants::class;
	private $utilsClass=ERPProductVariantsUtils::class;
    /**
     * @Route("/{_locale}/admin/global/{id}/productvariants", name="indexProductVariants")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new ERPProductVariantsUtils();
  		$templateLists[]=$utils->formatListbyProduct($id);
		//	$formUtils=new GlobaleFormUtils();
		//	$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/ProductVariants.json", $request, $this, $this->getDoctrine());

	//		$templateForms[]=$formUtils->formatForm('productvariants', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				return $this->render('@Globale/list.html.twig', [
					'listConstructor' => $templateLists
					]);
			}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/productvariantsc/data/{id}/{action}", name="dataProductVariants", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/ProductVariants.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formProduct");
		}

		/**
		 * @Route("/api/global/productvariants/{id}/list", name="productvariantslistproduct")
		 */
		public function productvariantslistproduct($id,RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$product = $productRepository->find($id);
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariants.json"),true);
			$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"product", "value"=>$product]]);
			return new JsonResponse($return);
		}



    /**
    * @Route("/api/global/productvariants/{id}/get", name="getProductVariant")
    */
    public function getProductVariant($id){
      $productvariant = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$productvariant ) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($productvariant->encodeJson());
    }

  /**
   * @Route("/api/productvariant/list", name="productvariantlist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductVariants.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ProductVariants::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/productvariant/{id}/disable", name="disableProductVariant")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/productvariant/{id}/enable", name="enableProductVariant")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/productvariant/{id}/delete", name="deleteProductVariant")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
