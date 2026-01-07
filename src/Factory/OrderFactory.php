<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * This factory decorates the order factory to set the token on the order
 * because we need this token if we want to reinstate a cart to a customer.
 *
 * @implements FactoryInterface<OrderInterface>
 */
final class OrderFactory implements FactoryInterface
{
    /** @param FactoryInterface<OrderInterface> $decorated */
    public function __construct(
        private readonly FactoryInterface $decorated,
        private readonly OrderTokenAssignerInterface $orderTokenAssigner,
    ) {
    }

    public function createNew(): object
    {
        $order = $this->decorated->createNew();

        if ($order instanceof OrderInterface) {
            $this->orderTokenAssigner->assignTokenValueIfNotSet($order);
        }

        return $order;
    }
}
