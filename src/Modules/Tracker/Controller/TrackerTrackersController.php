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
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Tracker\Utils\TrackerTrackersUtils;


class TrackerTrackersController extends Controller
{
  private $class=TrackerTrackers::class;
  private $classUtils=TrackerTrackersUtils::class;

  /**
   * @Route("/{_locale}/trackers/tracker", name="trackers")
   */
  public function index(RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  //$this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new $this->classUtils();
  $templateLists[]=$utils->formatList($this->getUser());
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Trackers.json", $request, $this, $this->getDoctrine());
  $templateForms[]=$formUtils->formatForm('trackers', true, null, $this->class);

  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'controllerName' => 'trackersController',
      'interfaceName' => 'Trackers',
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
   * @Route("/{_locale}/trackers/tracker/locations", name="trackersLocations")
   */
  public function trackersLocations(RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  //$this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Tracker/locations.html.twig', [
      'controllerName' => 'trackersController',
      'interfaceName' => 'Trackers',
      'optionSelected' => 'trackers',
      'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
      'breadcrumb' =>  'trackers',
      'userData' => $userdata
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
   * @Route("/{_locale}/trackers/data/{id}/{action}", name="dataTrackers", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/Trackers.json";
   $utils = new GlobaleFormUtils();
   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
   return $utils->make($id, $this->class, $action, "formTrackers", "modal");
  }

  /**
   * @Route("/api/trackers/list", name="trackerslist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Trackers.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class);
    return new JsonResponse($return);
  }

  /**
  * @Route("/{_locale}/tracker/trackers/{id}/disable", name="disableTracker")
  */
  public function disable($id)
   {
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/tracker/trackers/{id}/enable", name="enableTracker")
  */
  public function enable($id)
   {
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/tracker/trackers/{id}/delete", name="deleteTracker")
  */
  public function delete($id){
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }

}
