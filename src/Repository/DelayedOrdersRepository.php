<?php

namespace App\Repository;

use App\Entity\DelayedOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DelayedOrders>
 *
 * @method DelayedOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method DelayedOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method DelayedOrders[]    findAll()
 * @method DelayedOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DelayedOrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DelayedOrders::class);
    }

    public function save(DelayedOrders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DelayedOrders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDelayedOrdersBetweenDates(string $startDate = "", string $endDate = ""): array
    {
        $queryBuilder = $this->createQueryBuilder('o');

        if (!empty($startDate)) {
            $queryBuilder = $queryBuilder->andWhere('o.expected_delivery_time >= :start')->setParameter('start', $startDate);
        }

        if (!empty($endDate)) {
            $queryBuilder = $queryBuilder->andWhere('o.expected_delivery_time <= :end')->setParameter('end', $endDate);
        }
  
        $queryBuilder = $queryBuilder->orderBy('o.expected_delivery_time', 'ASC')
            ->getQuery();

        return $queryBuilder->getResult();
    }

//    /**
//     * @return DelayedOrders[] Returns an array of DelayedOrders objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DelayedOrders
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
