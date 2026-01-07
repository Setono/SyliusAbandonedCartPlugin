<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class UnsubscribedCustomerFactory implements UnsubscribedCustomerFactoryInterface
{
    /** @param FactoryInterface<UnsubscribedCustomerInterface> $decorated */
    public function __construct(private readonly FactoryInterface $decorated)
    {
    }

    public function createNew(): UnsubscribedCustomerInterface
    {
        return $this->decorated->createNew();
    }

    public function createWithEmail(string $email): UnsubscribedCustomerInterface
    {
        $obj = $this->createNew();
        $obj->setEmail($email);

        return $obj;
    }
}
