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
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPMyBackpackController extends Controller
{
	private $module='ERP';
	private $class=ERPMyBackpack::class;
	private $utilsClass=ERPMyBackpackUtils::class;

    /**
     * @Route("/{_locale}/ERP/mybackpack/add/{product}/{location}/{quantity}", name="mybackpackadd")
     */
    public function index($id, RouterInterface $router, Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;



  		return new Response('');
    }



}
