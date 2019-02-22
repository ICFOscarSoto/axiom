<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Globale\Entity\Zones;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ZonesUtils;

class ZonesController extends Controller
{
	private $class=Zones::class;

    /**
     * @Route("/{_locale}/admin/global/zones", name="zones")
     */
    public function index(RouterInterface $router,Request $request)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
    	$utils = new ZonesUtils();
  		$templateLists[]=$utils->formatList($this->getUser());
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'currenciesController',
  				'interfaceName' => 'Zonas',
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
    * @Route("/{_locale}/admin/global/zones/new", name="newZone")
    */

    public function newZone(Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $obj=new Zones();
      $utils = new ZonesUtils();
      $editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "New", "fa fa-plus");
      return $this->render($editor["template"], $editor["vars"]);
    }

    /**
    * @Route("/{_locale}/admin/global/zones/{id}/edit", name="editZone")
    */
    public function editZone($id,Request $request)
      {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $repository = $this->getDoctrine()->getRepository($this->class);
        $obj=$repository->find($id);
        $utils = new ZonesUtils();
        $editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "Edit", "fa fa-edit");
        return $this->render($editor["template"], $editor["vars"]);
    }

    /**
    * @Route("/api/global/zones/{id}/get", name="getZones")
    */
    public function getZone($id){
      $zone = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$zone) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($zone->encodeJson());
    }

  /**
   * @Route("/api/zones/list", name="zonelist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(Zones::class);
    $listUtils=new ListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Zones.json"),true);
    $return=$listUtils->getRecords($repository,$request,$manager,$listFields, Zones::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/zones/{id}/disable", name="disableZone")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/zones/{id}/enable", name="enableZone")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/zones/{id}/delete", name="deleteZone")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
