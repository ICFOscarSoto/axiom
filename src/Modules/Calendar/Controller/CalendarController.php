<?php
namespace App\Modules\Calendar\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Globale\MenuOptions;
use App\Entity\Globale\Users;
use App\Modules\Calendar\Entity\CalendarCalendars;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

class CalendarController extends Controller{
  /**
	 * @Route("/{_locale}/admin/calendar", name="calendar")
	 */
	public function calendar(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			return $this->render('@Calendar/calendar_list.html.twig', [
				'controllerName' => 'CalendarController',
				'interfaceName' => 'Calendario',
				'optionSelected' => 'calendar',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata
				]);
		}else return new RedirectResponse($this->router->generate('app_login'));
	}

  /**
  * @Route("/api/calendars/list", name="calendarsList")
  */
  public function calendarsList(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $calendarRepository = $this->getDoctrine()->getRepository(Calendars::class);
      $return=array();
      $user=$this->getUser();
      foreach($user->getCalendars() as $calendar){
          $item["id"] = $calendar->getId();
          $item["name"] = $calendar->getName();
          $item["color"] = $calendar->getColor();
					$return[]=$item;
      }

			return new JsonResponse($return);
    }
    return new Response();
  }
}
