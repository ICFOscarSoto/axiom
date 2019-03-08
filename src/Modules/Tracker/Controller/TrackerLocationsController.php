<?php
namespace App\Modules\Tracker\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Tracker\Entity\TrackerTrackers;
use App\Modules\Tracker\Entity\TrackerLocations;
use App\Modules\Tracker\Utils\TrackerLocationsUtils;

class TrackerLocationsController extends Controller
{
  private $class=TrackerLocations::class;
  private $classUtils=TrackerLocationsUtils::class;

  /**
   * @Route("/{_locale}/trackers/locations/location/{id}", name="locations", defaults={"id"=0})
   */
  public function index($id,RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  //$this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new $this->classUtils();
  $templateLists[]=$utils->formatList($this->getUser(), $id);
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Locations.json", $request, $this, $this->getDoctrine());
  $templateForms[]=$formUtils->formatForm('locations', true, null, $this->class);

  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'controllerName' => 'trackersLocationsController',
      'interfaceName' => 'Locations',
      'optionSelected' => $request->attributes->get('_route'),
      'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
      'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
      'userData' => $userdata,
      'lists' => $templateLists,
      'forms' => $templateForms
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
  * @Route("/api/tracker/doclocation/{company}/{id}", name="doLocations")
  */
  public function doLocations($company,$id, Request $request){

  }

  /**
   * @Route("/{_locale}/trackers/locations/data/{id}/{action}", name="dataLocations", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/Locations.json";
   $utils = new GlobaleFormUtils();
   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
   return $utils->make($id, $this->class, $action, "formLocations", "modal");
  }

  /**
   * @Route("/api/trackers/locations/{id}/list", name="locationslist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $trackerRepository=$this->getDoctrine()->getRepository(TrackerTrackers::class);
    $tracker = $trackerRepository->find($id);
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Locations.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class, [["type"=>"and", "column"=>"tracker", "value"=>$tracker]]);
    return new JsonResponse($return);
  }

  /**
  * @Route("/{_locale}/tracker/locations/{id}/disable", name="disableLocation")
  */
  public function disable($id)
   {
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/tracker/locations/{id}/enable", name="enableLocation")
  */
  public function enable($id)
   {
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/tracker/locations/{id}/delete", name="deleteLocation")
  */
  public function delete($id){
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }

}
