<?php

namespace App\Repository;

use App\Entity\OrderItems;
use App\Entity\Orders;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 *
 * @method Orders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orders[]    findAll()
 * @method Orders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    public function save(Orders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Orders $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Save order based on the inputs given
     * This inserts records in two tables, orders and order items
     */
    public function saveOrder(int $customerId, string $deliveryAddress, string $billingAddress, array $items): DateTime
    {
        $now = new \DateTime('now');

        $timeToDeliver = rand(1, 10);
        $deliveryDate = new \DateTime('now + ' . $timeToDeliver . ' days');
        
        $newOrder = new Orders();

        $newOrder
            ->setCustomerId($customerId)
            ->setDeliveryAddress($deliveryAddress)
            ->setBillingAddress($billingAddress)
            ->setExpectedDeliveryTime($deliveryDate)
            ->setStatus('NEW')
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->getEntityManager()->persist($newOrder);
        $this->getEntityManager()->flush();

        foreach ($items as $item) {
            $orderItem = new OrderItems();
            $productId = $item['productId'];
            $quantity = $item['quantity'];

            $orderItem
                ->setOrder($newOrder)
                ->setProductId($productId)
                ->setQuantity($quantity)
                ->setCreatedAt($now)
                ->setUpdatedAt($now);
            
            $this->getEntityManager()->persist($orderItem);
            $this->getEntityManager()->flush();
        }

        return $deliveryDate;
    }

    /**
     * Update the given Order object
     */
    public function updateOrder(Orders $order): Orders
    {
        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();
    
        return $order;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'customerId' => $this->getCustomerId(),
            'deliveryAddress' => $this->getDeliveryAddress(),
            'billingAddress' => $this->getBillingAddress(),
            'status' => $this->getStatus(),
            'expectedDeliveryTime' => $this->getExpectedDeliveryTime()->format('Y:m:d H:i:s'),
            'createdAt' => $this->getCreatedAt()->format('Y:m:d H:i:s'),
            'updatedAt' => $this->getUpdatedAt()->format('Y:m:d H:i:s'),
        ];
    }

//    /**
//     * @return Orders[] Returns an array of Orders objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Orders
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
