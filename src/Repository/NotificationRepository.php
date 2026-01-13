<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use DateTimeInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Order\Model\OrderInterface;

class NotificationRepository extends EntityRepository implements NotificationRepositoryInterface
{
    public function removeOlderThan(DateTimeInterface $threshold): void
    {
        $this->createQueryBuilder('o')
            ->delete()
            ->andWhere('o.createdAt <= :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute()
        ;
    }

    public function findOneByOrder(OrderInterface $order): ?NotificationInterface
    {
        /** @var NotificationInterface|null $notification */
        $notification = $this->findOneBy(['cart' => $order]);

        return $notification;
    }
}
