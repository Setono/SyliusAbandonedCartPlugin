<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Sylius\Component\Core\Model\OrderInterface;

interface CartRecoveryUrlGeneratorInterface
{
    /**
     * Generates the URL for the customer to visit to recover his/her cart
     */
    public function generate(
        OrderInterface $order,
        array $parameters = [],
    ): string;
}
