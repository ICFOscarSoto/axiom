<?php

namespace App\Modules\IoT\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\IoT\Entity\IoTDevices;
use App\Modules\IoT\Entity\IoTSensors;
use App\Modules\IoT\Entity\IoTData;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
class IoTController extends Controller
{
	private $class=EmailsSubjects::class;
	static function cmpTimestamp($a, $b){ return strcmp($a["timestamp"], $b["timestamp"]);}
	/**
	 * @Route("/{_locale}/iot/devices", name="devices")
	 */
	public function devices(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {

			return new Response('');
		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/api/iot/data", name="devices")
	 */
	public function data(RouterInterface $router,Request $request){
			$devicesRepository=$this->getDoctrine()->getRepository(IoTDevices::class);
			$sensorsRepository=$this->getDoctrine()->getRepository(IoTSensors::class);
			$token=$request->get("t");
			$device=$devicesRepository->findOneBy(['token'=>$token, 'active'=>1, 'deleted'=>0]);
			if(!$device) return new JsonResponse(["result"=>-1]);
			$params=$request->get("p");
			$values=$request->get("v");
			$params_array=explode('|', $params);
			$values_array=explode('|', $values);
			foreach($params_array as $key=>$param){
				$sensor=$sensorsRepository->findOneBy(['device'=>$device, 'name'=>$param, 'active'=>1, 'deleted'=>0]);
				if(!$sensor) continue;
				$data = new IoTData();
				$data->setSensor($sensor);
				$data->setCounter(1);
				$data->setData($values_array[$key]/(pow(10,$sensor->getAccuracy())));
				$data->setDateadd(new \DateTime());
				$data->setDateupd(new \DateTime());
				$data->setActive(1);
				$data->setDeleted(0);
				$this->getDoctrine()->getManager()->persist($data);
				$this->getDoctrine()->getManager()->flush();
			}
			return new JsonResponse(["result"=>1]);
	}

	/**
	 * @Route("/api/iot/{id}/lastdata", name="Iotlastsensordata")
	 */
	public function lastdata($id,RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$devicesRepository=$this->getDoctrine()->getRepository(IoTDevices::class);
			$sensorsRepository=$this->getDoctrine()->getRepository(IoTSensors::class);
			$dataRepository=$this->getDoctrine()->getRepository(IoTData::class);
			$device=$devicesRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'active'=>1, 'deleted'=>0]);
			if(!$device) return new JsonResponse(["result"=>-1]);
			$sensors=$sensorsRepository->findBy(['device'=>$device, 'active'=>1, 'deleted'=>0]);
			$result=[];
			foreach($sensors as $sensor){
				$data=$dataRepository->getLastData($sensor->getId());
				$status='norminal';
				if($sensor->getMax() && $data["data"]>$sensor->getMax()){
					$status='failure';
				}
				if($sensor->getMin() && $data["data"]<$sensor->getMin()){
					$status='failure';
				}
				$result[]=["id"=>$sensor->getId(), "name"=>$sensor->getName(), "description"=> $sensor->getDescription(),"value"=>$data["data"], "type"=>$sensor->getType(), "unit"=>$sensor->getUnit(), "unit_abrv"=> $sensor->getUnitAbrv(), "status"=>$status, "date"=>$data["dateupd"]];
			}
			return new JsonResponse(["result"=>1, "data"=> $result]);
	}
}
