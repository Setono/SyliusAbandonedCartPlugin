<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Factory;

use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class UnsubscribedCustomerFactory implements UnsubscribedCustomerFactoryInterface
{
    public function __construct(private readonly FactoryInterface $decorated)
    {
    }

    public function createNew(): UnsubscribedCustomerInterface
    {
        /** @var UnsubscribedCustomerInterface|object $obj */
        $obj = $this->decorated->createNew();
        Assert::isInstanceOf($obj, UnsubscribedCustomerInterface::class);

        return $obj;
    }

    public function createWithEmail(string $email): UnsubscribedCustomerInterface
    {
        $obj = $this->createNew();
        $obj->setEmail($email);

        return $obj;
    }
}
