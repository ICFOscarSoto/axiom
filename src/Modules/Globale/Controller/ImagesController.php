<?php
namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Modules\Globale\Entity\Users;

class ImagesController extends Controller
{

	/**
     * @Route("/api/files/{ext}/getimage", name="getFilesImage")
     */
	public function getFilesImage($ext, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$image_path = $this->get('kernel')->getRootDir() . '/../public/images/filetype/';
			if(file_exists($image_path.$ext.'.png'))
				$filename = $ext.'.png';
			else $filename = 'file.png';
			$response = new BinaryFileResponse($image_path.$filename);
			$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
			if($mimeTypeGuesser->isSupported()){
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($image_path.$filename));
			}else{
				$response->headers->set('Content-Type', 'text/plain');
			}
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$filename
			);
			return $response;
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	* @Route("/api/user/{id}/getimage", name="getUserImage")
	*/
	public function get_user_imageId($id, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$currentUser=$this->getUser();
			$userRepository = $this->getDoctrine()->getRepository(Users::class);
			if ($this->get('security.authorization_checker')->isGranted('ROLE_GLOBAL')) {
				$user=$userRepository->find($id);
			}else{
				$user=$userRepository->findOneBy([
						'id' => $id,
						'$company' => $currecurrentUser->getCompany()->getId()
					]);
			}
			$image_path = $this->get('kernel')->getRootDir() . '/../public/images/users/';
			$filename="";
			if($user!==null){
				$image_path = $this->get('kernel')->getRootDir() . '/../public/images/users/';
				if(file_exists($image_path.$user->getId().'-thumb.png'))
					$filename = $user->getId().'-thumb.png';
				else if(file_exists($image_path.$user->getId().'-thumb.jpg'))
					$filename = $user->getId().'-thumb.jpg';
				else $filename = 'no-thumb.jpg';
			}else $filename = 'no-thumb.jpg';
			$response = new BinaryFileResponse($image_path.$filename);
			$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
			if($mimeTypeGuesser->isSupported()){
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($image_path.$filename));
			}else{
				$response->headers->set('Content-Type', 'text/plain');
			}
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$filename
			);

			return $response;
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}
}
