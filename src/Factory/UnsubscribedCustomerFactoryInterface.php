<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/** @extends FactoryInterface<UnsubscribedCustomerInterface> */
interface UnsubscribedCustomerFactoryInterface extends FactoryInterface
{
    public function createNew(): UnsubscribedCustomerInterface;

    public function createWithEmail(string $email): UnsubscribedCustomerInterface;
}
