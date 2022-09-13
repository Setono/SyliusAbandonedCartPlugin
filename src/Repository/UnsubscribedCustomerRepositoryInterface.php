<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Repository;

use Sylius\Component\Resource\Repository\RepositoryInterface;

interface UnsubscribedCustomerRepositoryInterface extends RepositoryInterface
{
    /**
     * Returns true if the given email is unsubscribed
     */
    public function isUnsubscribed(string $email): bool;
}
