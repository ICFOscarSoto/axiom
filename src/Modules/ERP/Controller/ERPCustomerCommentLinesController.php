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
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPCustomersUtils;
use App\Modules\ERP\Utils\ERPCustomerCommentLinesUtils;
use App\Modules\ERP\Entity\ERPCustomer;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;

class ERPCustomerCommentLinesController extends Controller
{

		private $class=ERPCustomerCommentLines::class;
		private $utilsClass=ERPCustomerCommentLinesUtils::class;
    /**
   	* @Route("/api/customercommentlines/list/{customerid}/{type}", name="customercommentlineslist")
   	*/
    public function customercommentlineslist(RouterInterface $router,Request $request, $customerid, $type){
		//dump($customerid);
	//	dump($type);
   	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   	 $user = $this->getUser();
   	 $locale = $request->getLocale();
   	 $this->router = $router;
   	 $manager = $this->getDoctrine()->getManager();
   	 $repository = $manager->getRepository($this->class);
   	 $listUtils=new GlobaleListUtils();
   	 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CustomerCommentLines.json"),true);

   	 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, CustomerCommentLines::class,[["type"=>"and", "column"=>"customer", "value"=>$customerid],["type"=>"and", "column"=>"type", "value"=>$type]]);
		 return new JsonResponse($return);
    }

}
