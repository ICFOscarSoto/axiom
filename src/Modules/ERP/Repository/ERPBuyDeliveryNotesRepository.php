<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyDeliveryNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Modules\ERP\Entity\ERPSupplierCommentLines;

/**
 * @method ERPBuyDeliveryNotes|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyDeliveryNotes|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyDeliveryNotes[]    findAll()
 * @method ERPBuyDeliveryNotes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyDeliveryNotesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyDeliveryNotes::class);
    }

    // /**
    //  * @return ERPBuyDeliveryNotes[] Returns an array of ERPBuyDeliveryNotes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ERPBuyDeliveryNotes
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    // Último código utilizado
    public function getLastID(){
      $query='SELECT max(id) FROM erpbuy_delivery_notes';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);
    }

    // Obtiene el siguiente código a utilizar en la creación de un albarán de compra
    public function getNextCode(){
      $query='SELECT CAST(SUBSTRING(max(CODE),5) AS UNSIGNED) AS result
      FROM erpbuy_delivery_notes WHERE SUBSTRING(CODE,1,2)=SUBSTRING(year(NOW()),3,2)';
      $id = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);
      if ($id==null)
        $id = 0;
      $id++;
      return date('y').'AC'.str_pad($id,5,'0',STR_PAD_LEFT);
    }

}
