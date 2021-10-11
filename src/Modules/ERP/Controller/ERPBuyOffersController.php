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

class ERPBuyOffersController extends Controller
{
	private $class=ERPBuyOffers::class;
	private $utilsClass=ERPBuyOffersUtils::class;


  /**
   * @Route("/{_locale}/ERP/gridproduct/autocomplete", name="gridProductAutocomplete")
   */
  public function gridProductAutocomplete(RouterInterface $router,Request $request)
  {
		$q=$request->query->get("q","");
		return new JsonResponse([["text"=>$q, "value"=>$q]]);

  }


	/**
   * @Route("/{_locale}/ERP/buyoffer/form", name="buyoffer")
   */
  public function index(RouterInterface $router,Request $request)
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $locale = $request->getLocale();
    $this->router = $router;

      return $this->render('@ERP/buyoffer.html.twig', []);

  }
}
