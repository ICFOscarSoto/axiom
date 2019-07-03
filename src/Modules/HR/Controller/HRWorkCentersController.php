<?php
namespace App\Modules\HR\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\HR\Utils\HRWorkCentersUtils;
use App\Modules\HR\Entity\HRWorkCenters;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HRWorkCentersController extends Controller
{
  private $class=HRWorkCenters::class;
  private $utilsClass=HRWorkCentersUtils::class;

  /**
   * @Route("/{_locale}/HR/workcenter/{id}/workers", name="workcenterworkers")
   */
  public function workcenterworkers($id,RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new HRWorkCentersUtils();
  $templateLists[]=$utils->formatListWorkers($this->getUser(), $id);

  $repository = $this->getDoctrine()->getRepository($this->class);
  $obj=$repository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
  $entityName=$obj?$obj->getName():'';
  $breadcrumb=$menurepository->formatBreadcrumb('genericindex', "HR", "workCenters");
  array_push($breadcrumb, ["rute"=>null, "name"=>$entityName, "icon"=>"fa fa-address-book-o"]);
  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'entity_name' => $entityName,
      'controllerName' => 'HRController',
      'interfaceName' => 'Trabajadores',
      'optionSelected' => "genericindex",
      'optionSelectedParams' => ["module"=>"HR", "name"=>"workCenters"],
      'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
      'breadcrumb' =>  $breadcrumb,
      'userData' => $userdata,
      'lists' => $templateLists,
      'include_post_templates' => ['@HR/clocksprintselect.html.twig'],
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }
}
