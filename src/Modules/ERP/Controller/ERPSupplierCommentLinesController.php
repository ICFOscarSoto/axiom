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
use App\Modules\ERP\Utils\ERPSuppliersUtils;
use App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPSupplierCommentLines;

class ERPSupplierCommentLinesController extends Controller
{

		private $class=ERPSupplierCommentLines::class;
		private $utilsClass=ERPSupplierCommentLinesUtils::class;
    /**
   	* @Route("/api/suppliercommentlines/list/{supplierid}/{type}", name="suppliercommentlineslist")
   	*/
    public function suppliercommentlineslist(RouterInterface $router,Request $request, $supplierid, $type){
   	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   	 $user = $this->getUser();
   	 $locale = $request->getLocale();
   	 $this->router = $router;
   	 $manager = $this->getDoctrine()->getManager();
   	 $repository = $manager->getRepository($this->class);
   	 $listUtils=new GlobaleListUtils();
   	 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SupplierCommentLines.json"),true);
   	 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, CustomerGroups::class,[["type"=>"and", "column"=>"supplier", "value"=>$supplierid],["type"=>"and", "column"=>"type", "value"=>$type]]);
   	 return new JsonResponse($return);
    }

}
