<?php
namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleComments;

class GlobaleCommentsController extends Controller
{
  private $module='Globale';
  private $class=GlobaleComments::class;

  /**
   * @Route("/api/globale/savecomment/{id}", name="saveComment", defaults={"id"=0})
   */
  public function saveComment($id,RouterInterface $router,Request $request){
	      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $commentsRepository	= $this->getDoctrine()->getRepository(GlobaleComments::class);
        $comment = null;

        $entity=$request->request->get('entity');
        $entity_id=$request->request->get('entity_id');
        $comment_text=$request->request->get('comment');

        //Comprobar que existe la entidad y el objeto
        if(!class_exists('\\'.$entity)) return new JsonResponse(["result"=>-1]);
        $repository	= $this->getDoctrine()->getRepository('\\'.$entity);
        if(!$repository) return new JsonResponse(["result"=>-1]);
        $obj = $repository->find($entity_id);
        if(!$obj) return new JsonResponse(["result"=>-1]);

        if($id!=0){
          $comment=$commentsRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
          //Comprobamos que el usuario sea el autor del comentario
          if(!$comment || $comment->getUser() != $this->getUser())
            return new JsonResponse(["result"=>-1]);
        }
        if(!$comment){
          $comment= new GlobaleComments();
          $comment->setCompany($this->getUser()->getCompany());
          $comment->setUser($this->getUser());
          $comment->setEntity($entity);
          $comment->setEntityId($entity_id);
          $comment->setActive(1);
          $comment->setDeleted(0);
          $comment->setDateadd(new \DateTime());
        }
        $comment->setComment($comment_text);
        $comment->setDateupd(new \DateTime());
        $this->getDoctrine()->getManager()->persist($comment);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse(["result"=>1, "id"=>$comment->getId()]);
  }
}
