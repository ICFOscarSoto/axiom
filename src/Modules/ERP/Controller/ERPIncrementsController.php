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
use App\Modules\ERP\Entity\ERPIncrements;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPIncrementsUtils;
use App\Modules\ERP\Utils\ERPSuppliersUtils;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPSuppliers;

class ERPIncrementsController extends Controller
{

		private $class=ERPIncrements::class;
		private $utilsClass=ERPIncrementsUtils::class;
		/**
		* @Route("/api/globale/suppliercategoriestrigger", name="suppliercategoriestrigger", defaults={"id"=0})
		*/
		public function suppliercategoriestrigger(Request $request){
					$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
					 $id = $request->request->get("id");
					 $referencesRepository = $this->getDoctrine()->getRepository(ERPReferences::class);
					 $products_ids=$referencesRepository->ProductsBySupplier($id);
					//dump($products_ids);
					 foreach($products_ids as $product_id){
						 $productsRepository = $this->getDoctrine()->getRepository(ERPProducts::class);
						 $productObj=$productsRepository->findOneBy(["id"=>$product_id]);
						 $categoria=$productObj->getCategory();
						 $arrayCat[]=$categoria;
			 	 	}

			 $return=[];

			// dump($arrayCat);
			 foreach($arrayCat as $category){

				 if($category->getParentid()!=NULL) $category=$category->getParentid();
				 while($category->getParentid()!=NULL)
				 {
						$arrayCat[]=$category;
						$category=$category->getParentid();
				 }
				 $arrayCat[]=$category;

			 }
			// $array_total= array_values(array_unique($arrayCat));
			$aux=[];
			 foreach($arrayCat as $category){
			   if(in_array($category,$aux)==false)
				 {
					 	$option["id"]=$category->getId();
					 	$option["text"]=$category->getName();
					 	$return[]=$option;
					 	array_push($aux,$category);
					}

				}
	 				return new JsonResponse($return);


		 }


}
