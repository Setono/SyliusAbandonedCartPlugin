<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Order\Model\OrderInterface;

class NotificationRepository extends EntityRepository implements NotificationRepositoryInterface
{
    public function findNew(DateInterval $idleThreshold): array
    {
        $updatedAtThreshold = (new DateTimeImmutable())->sub($idleThreshold);

        /** @var list<NotificationInterface> $objs */
        $objs = $this->createQueryBuilder('o')
            ->join('o.cart', 'c')
            ->andWhere('o.state = :state')
            ->andWhere('c.state = :orderState')
            ->andWhere('c.customer is not null') // we don't have the email if the customer is null
            ->andWhere('c.updatedAt <= :updatedAtThreshold')
            ->setParameter('state', NotificationWorkflow::STATE_INITIAL)
            ->setParameter('orderState', OrderInterface::STATE_CART)
            ->setParameter('updatedAtThreshold', $updatedAtThreshold)
            ->setMaxResults(100) // we have this to avoid any memory problems when fetching notifications
            ->getQuery()
            ->getResult()
        ;

        return $objs;
    }

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
}
