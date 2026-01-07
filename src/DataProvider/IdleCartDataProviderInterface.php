<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DataProvider;

use Sylius\Component\Core\Model\OrderInterface;

interface IdleCartDataProviderInterface
{
    /**
     * Returns idle carts that don't have an associated notification yet.
     *
     * @return iterable<OrderInterface>
     */
    public function getCarts(): iterable;
}
