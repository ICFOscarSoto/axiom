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
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPSalesTicketsHistory;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSalesTicketsUtils;
use App\Modules\ERP\Utils\ERPSalesTicketsHistoryUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPSalesTicketsHistoryController extends Controller
{
		private $class=ERPSalesTicketsHistory::class;
		private $utilsClass=ERPSalesTicketsHistoryUtils::class;
		private $module='ERP';

		/**
		* @Route("/api/salesticketshistory/list/{salesticketid}", name="salesticketshistorylist")
		*/
		public function salesticketshistorylist(RouterInterface $router,Request $request, $salesticketid){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $user = $this->getUser();
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTicketsHistory.json"),true);
		 //dump($salesticketsid);
		 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSalesTicketsHistory::class,[["type"=>"and", "column"=>"salesticket", "value"=>$salesticketid]]);
		 return new JsonResponse($return);
		}



}
