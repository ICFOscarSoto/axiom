<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Entity\GlobaleWidgets;
use App\Modules\Globale\Entity\GlobaleUsersWidgets;
class GlobaleWidgetsController extends Controller
{
	private $class=GlobaleWidgets::class;

	/**
	 * @Route("/api/global/widgets/updatelayout", name="updatelayout")
	 */
	public function updatelayout(RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$layout=$request->request->get('layout');
		$widgetsRepository=$this->getDoctrine()->getRepository(GlobaleWidgets::class);
		$usersWidgetsRepository=$this->getDoctrine()->getRepository(GlobaleUsersWidgets::class);
		$result=["result"=>1];
		foreach ($layout as $key => $widget) {
			if(isset($widget["id"])){ //Because the add buton hasn't id
					$userWidget=$usersWidgetsRepository->findOneBy(["id"=>$widget["id"], "user"=>$this->getUser(),"active"=>1,"deleted"=>0]);
					if($userWidget){
							$userWidget->setX($widget["x"]);
							$userWidget->setY($widget["y"]);
							$userWidget->setDateupd(new \DateTime());
							$this->getDoctrine()->getManager()->persist($userWidget);
							$this->getDoctrine()->getManager()->flush();
						}
				}
		}

		return new JsonResponse($result);
	}

	/**
	 * @Route("/api/global/widgets/delete", name="deletewidget")
	 */
	public function deletewidget(RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$id=$request->request->get('id');
		$widgetsRepository=$this->getDoctrine()->getRepository(GlobaleWidgets::class);
		$usersWidgetsRepository=$this->getDoctrine()->getRepository(GlobaleUsersWidgets::class);
		$result=["result"=>1];
		$userWidget=$usersWidgetsRepository->findOneBy(["id"=>$id, "user"=>$this->getUser(),"active"=>1,"deleted"=>0]);
		$userWidget->setActive(0);
		$userWidget->setDeleted(1);
		$userWidget->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($userWidget);
		$this->getDoctrine()->getManager()->flush();

		return new JsonResponse($result);
	}

	/**
	 * @Route("/api/global/widgets/add", name="addwidget")
	 */
	public function addwidget(RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$id=$request->request->get('id');
		$widgetsRepository=$this->getDoctrine()->getRepository(GlobaleWidgets::class);
		$usersWidgetsRepository=$this->getDoctrine()->getRepository(GlobaleUsersWidgets::class);
		$result=["result"=>1];
		$widget=$widgetsRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
		$widgetConfigRepository=$this->getDoctrine()->getRepository("\App\Widgets\Entity\Widgets".$widget->getName());

		//Create widget
		$userWidget=new GlobaleUsersWidgets();
		$userWidget->setUser($this->getUser());
		$userWidget->setWidget($widget);
		$userWidget->setW($widget->getW());
		$userWidget->setH($widget->getH());
		$userWidget->setX(0);
		$userWidget->setY(0);
		$userWidget->setActive(1);
		$userWidget->setDeleted(0);
		$userWidget->setDateadd(new \DateTime());
		$userWidget->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($userWidget);
		$this->getDoctrine()->getManager()->flush();

		//Create Widget Config
		$classConfig="\App\Widgets\Entity\Widgets".$widget->getName();
		$userWidgetConfig=new $classConfig();
		$userWidgetConfig->setUserwidget($userWidget);
		$userWidgetConfig->setActive(1);
		$userWidgetConfig->setDeleted(0);
		$userWidgetConfig->setDateadd(new \DateTime());
		$userWidgetConfig->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($userWidgetConfig);
		$this->getDoctrine()->getManager()->flush();

		return new JsonResponse($result);
	}

}
