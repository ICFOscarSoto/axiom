<?php
namespace App\Utils\Globale;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EntityUtils
{
	public function disableObject($id, $class, $doctrine)
    {
		$object= $doctrine
			->getRepository($class)
			->find($id);
		$object->setActive(false);
		$manager = $doctrine->getManager();
        $manager->persist($object);
		$manager->flush();
		return true;
	}
	
	public function enableObject($id, $class, $doctrine)
    {
		$object= $doctrine
			->getRepository($class)
			->find($id);
		$object->setActive(true);
		$manager = $doctrine->getManager();
        $manager->persist($object);
		$manager->flush();
		return true;
	}
	
	
}