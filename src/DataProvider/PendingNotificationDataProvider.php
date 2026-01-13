<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DataProvider;

use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineBatchUtils\BatchProcessing\SelectBatchIteratorAggregate;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Sylius\Component\Order\Model\OrderInterface;

final class PendingNotificationDataProvider implements PendingNotificationDataProviderInterface
{
    use ORMTrait;

    /**
     * @param class-string $notificationClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly string $notificationClass,
        private readonly int $idleThresholdInMinutes,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function getNotifications(): iterable
    {
        $updatedAtThreshold = (new DateTimeImmutable())->modify(sprintf('-%d minutes', $this->idleThresholdInMinutes));

        $manager = $this->getManager($this->notificationClass);

        $qb = $manager
            ->createQueryBuilder()
            ->select('n')
            ->from($this->notificationClass, 'n')
            ->join('n.cart', 'c')
            ->andWhere('n.state = :state')
            ->andWhere('c.state = :orderState')
            ->andWhere('c.customer IS NOT NULL')
            ->andWhere('c.updatedAt <= :updatedAtThreshold')
            ->setParameter('state', NotificationWorkflow::STATE_PENDING)
            ->setParameter('orderState', OrderInterface::STATE_CART)
            ->setParameter('updatedAtThreshold', $updatedAtThreshold)
        ;

        /** @var SelectBatchIteratorAggregate<array-key, NotificationInterface> $batch */
        $batch = SelectBatchIteratorAggregate::fromQuery($qb->getQuery(), 100);

        yield from $batch;
    }
}
