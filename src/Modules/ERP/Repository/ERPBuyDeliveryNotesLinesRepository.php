<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyDeliveryNotesLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyDeliveryNotesLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyDeliveryNotesLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyDeliveryNotesLines[]    findAll()
 * @method ERPBuyDeliveryNotesLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyDeliveryNotesLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyDeliveryNotesLines::class);
    }

    // /**
    //  * @return ERPBuyDeliveryNotesLines[] Returns an array of ERPBuyDeliveryNotesLines objects
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
    public function findOneBySomeField($value): ?ERPBuyDeliveryNotesLines
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    // Pone linenum=0 a todas las líneas del pedido indicado
    public function setLinenum($buyorder_id){
      $query='update erpbuy_delivery_notes_lines set linenum=0 where buydeliverynote_id='.$buyorder_id;
      $this->getEntityManager()->getConnection()->executeQuery($query);
      return true;
    }
    // Elimina las líneas con linenum=0 del pedido indicado
    public function deleteLinenum($buyorder_id){
      $query='delete from erpbuy_delivery_notes_lines where linenum=0 and buydeliverynote_id='.$buyorder_id;
      $this->getEntityManager()->getConnection()->executeQuery($query);
      return true;
    }
}
