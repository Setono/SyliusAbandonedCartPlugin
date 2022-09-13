<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface CartRecoveryUrlGeneratorInterface
{
    /**
     * Generates the URL for the customer to visit to recover his/her cart
     */
    public function generate(
        OrderInterface $order,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ): string;
}
