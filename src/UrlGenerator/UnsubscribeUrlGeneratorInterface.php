<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface UnsubscribeUrlGeneratorInterface
{
    /**
     * Generates an unsubscribe URL for a given email
     */
    public function generate(
        string $email,
        string $locale,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ): string;
}
