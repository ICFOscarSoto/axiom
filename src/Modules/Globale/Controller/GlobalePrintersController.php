<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobalePrinters;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleWorkstations;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleActivities;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleActivitiesUtils;
use App\Modules\Security\Utils\SecurityUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

class GlobalePrintersController extends Controller
{
	private $module='Globale';
	private $class=GlobalePrinters::class;

	/**
	 * @Route("/api/global/printers/register/{deviceid}", name="registerPrinter")
	 */
	public function registerPrinter($deviceid, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$workstationRepository=$this->getDoctrine()->getRepository(GlobaleWorkstations::class);
		$printersRepository=$this->getDoctrine()->getRepository(GlobalePrinters::class);
		$workstation=$workstationRepository->findOneBy(['deviceid'=>$deviceid, 'active'=>1, 'deleted'=>0]);
		if(!$workstation) return new JsonResponse(["result"=>-1]);
		$name=$request->query->get('name');
		$type=$request->query->get('type');
		$size=$request->query->get('size');
		if(!$name || $type===null || $size===null) return new JsonResponse(["result"=>-2]);

		$printer=$printersRepository->findOneBy(['name'=>$name, 'workstation'=>$workstation, 'active'=>1, 'deleted'=>0]);
		if($printer) return new JsonResponse(["result"=>-3]);
		$printer=new GlobalePrinters();
		$printer->setCompany($this->getUser()->getCompany());
		$printer->setWorkstation($workstation);
		$printer->setName($name);
		$printer->setType($type);
		$printer->setSize($size);
		$printer->setActive(1);
		$printer->setDeleted(0);
		$printer->setDateadd(new \DateTime());
		$printer->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($printer);
		$this->getDoctrine()->getManager()->flush();

		$printDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'printers'.DIRECTORY_SEPARATOR.$printer->getId();
		if (!file_exists($printDir)) {
				mkdir($printDir, 0777, true);
		}
		return new JsonResponse(["result"=>1, "id"=>$printer->getId()]);
	}

	/**
	 * @Route("/api/global/printers/unregister/{deviceid}/{id}", name="unregisterPrinter")
	 */
	public function unregisterPrinter($deviceid, $id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$workstationRepository=$this->getDoctrine()->getRepository(GlobaleWorkstations::class);
		$printersRepository=$this->getDoctrine()->getRepository(GlobalePrinters::class);
		$workstation=$workstationRepository->findOneBy(['deviceid'=>$deviceid, 'active'=>1, 'deleted'=>0]);
		if(!$workstation) return new JsonResponse(["result"=>-1]);
		$printer=$printersRepository->findOneBy(['id'=>$id, 'active'=>1, 'deleted'=>0]);
		if(!$printer) return new JsonResponse(["result"=>-2]);
		$printer->setActive(0);
		$printer->setDeleted(1);
		$printer->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($printer);
		$this->getDoctrine()->getManager()->flush();
		$printDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'printers'.DIRECTORY_SEPARATOR.$printer->getId();
		if (!file_exists($printDir)) {
				unlink($printDir);
		}
		return new JsonResponse(["result"=>1]);
	}

	/**
	 * @Route("/api/global/printers/get/{deviceid}/{type}", name="getPrinters", defaults={"deviceid"=0, "type"=-1})
	 */
	public function getPrinters($deviceid, $type, RouterInterface $router,Request $request)
	{
		$workstationRepository=$this->getDoctrine()->getRepository(GlobaleWorkstations::class);
		$printersRepository=$this->getDoctrine()->getRepository(GlobalePrinters::class);

		//TODO get company for queries but be carefull because desktop app use this and is not logged :-(
		if($deviceid!=0){
			//Only printers of a device	
			$workstation=$workstationRepository->findOneBy(['deviceid'=>$deviceid, 'active'=>1, 'deleted'=>0]);
			if(!$workstation) return new JsonResponse(["result"=>-1]);
			$printers=$printersRepository->findBy(['workstation'=>$workstation, 'active'=>1, 'deleted'=>0]);
		}else{
			//All printers
			if($type==-1)
				$printers=$printersRepository->findBy(['active'=>1, 'deleted'=>0]);
			else $printers=$printersRepository->findBy(['type'=>$type, 'active'=>1, 'deleted'=>0]);
		}



		$arrayPrinters=[];
		foreach($printers as $printer){
			$item["id"]			=$printer->getId();
			$item["name"]		=$printer->getName();
			$item["type"]		=$printer->getType();
			$item["size"]		=$printer->getSize();
			$arrayPrinters[]=$item;
		}
		return new JsonResponse($arrayPrinters);
	}

  /**
   * @Route("/api/global/printers/getprints/{id}", name="getprints")
   */
  public function getprints($id, RouterInterface $router,Request $request)
  {
		$printersRepository=$this->getDoctrine()->getRepository(GlobalePrinters::class);
		$printer=$printersRepository->findOneBy(['id'=>$id, 'active'=>1, 'deleted'=>0]);
		if(!$printer) return new JsonResponse([]);
    $printDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$printer->getCompany()->getId().DIRECTORY_SEPARATOR.'printers'.DIRECTORY_SEPARATOR.$id;
		$prints  = scandir($printDir);
		array_splice($prints, 0, 2);
		return new JsonResponse($prints);
  }

	/**
	 * @Route("/api/global/print/download/{id}/{name}", name="downloadPrint", defaults={"name"=null})
	 */
	public function downloadPrint($id, $name){
		$printersRepository=$this->getDoctrine()->getRepository(GlobalePrinters::class);
		$printer=$printersRepository->findOneBy(['id'=>$id, 'active'=>1, 'deleted'=>0]);
		if(!$printer) return new JsonResponse(["result"=>-1]);
		$printDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$printer->getCompany()->getId().DIRECTORY_SEPARATOR.'printers'.DIRECTORY_SEPARATOR.$id;
		$filename=$printDir.DIRECTORY_SEPARATOR.$name;
		$response = new BinaryFileResponse($filename);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$name);
		return $response;
	}

	/**
	 * @Route("/api/global/print/remove/{id}/{name}", name="removePrint")
	 */
	public function removePrint($id, $name){
		$printersRepository=$this->getDoctrine()->getRepository(GlobalePrinters::class);
		$printer=$printersRepository->findOneBy(['id'=>$id, 'active'=>1, 'deleted'=>0]);
		if(!$printer) return new JsonResponse(["result"=>-1]);
		$printDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$printer->getCompany()->getId().DIRECTORY_SEPARATOR.'printers'.DIRECTORY_SEPARATOR.$id;
		$filename=$printDir.DIRECTORY_SEPARATOR.$name;
		unlink($filename);
		return new JsonResponse(["result"=>1]);
	}
}
