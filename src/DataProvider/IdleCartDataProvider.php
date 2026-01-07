<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DataProvider;

use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineBatchUtils\BatchProcessing\SelectBatchIteratorAggregate;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusAbandonedCartPlugin\Event\QueryBuilderForIdleCartsCreated;
use Sylius\Component\Core\Model\OrderInterface;

final class IdleCartDataProvider implements IdleCartDataProviderInterface
{
    use ORMTrait;

    /**
     * @param class-string $orderClass
     * @param class-string $notificationClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $orderClass,
        private readonly string $notificationClass,
        private readonly int $idleThresholdInMinutes,
        private readonly int $lookbackWindowInMinutes,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function getCarts(): iterable
    {
        $now = new DateTimeImmutable();

        // Cart must be idle for at least idle_threshold minutes
        $idleThreshold = $now->modify(sprintf('-%d minutes', $this->idleThresholdInMinutes));

        // But not older than (idle_threshold + lookback_window) minutes
        $lookbackThreshold = $now->modify(sprintf('-%d minutes', $this->idleThresholdInMinutes + $this->lookbackWindowInMinutes));

        $manager = $this->getManager($this->orderClass);

        $qb = $manager
            ->createQueryBuilder()
            ->select('o')
            ->from($this->orderClass, 'o')
            ->andWhere('o.state = :state')
            ->andWhere('o.customer IS NOT NULL')
            ->andWhere('o.updatedAt <= :idleThreshold')
            ->andWhere('o.updatedAt >= :lookbackThreshold')
            ->andWhere(sprintf('NOT EXISTS (SELECT 1 FROM %s n WHERE n.cart = o)', $this->notificationClass))
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('idleThreshold', $idleThreshold)
            ->setParameter('lookbackThreshold', $lookbackThreshold)
        ;

        $this->eventDispatcher->dispatch(new QueryBuilderForIdleCartsCreated($qb));

        /** @var SelectBatchIteratorAggregate<array-key, OrderInterface> $batch */
        $batch = SelectBatchIteratorAggregate::fromQuery($qb->getQuery(), 100);

        yield from $batch;
    }
}
