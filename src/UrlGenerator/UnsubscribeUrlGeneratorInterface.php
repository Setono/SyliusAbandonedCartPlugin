<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Sylius\Component\Channel\Model\ChannelInterface;

interface UnsubscribeUrlGeneratorInterface
{
    /**
     * Generates an unsubscribe URL for a given email
     */
    public function generate(
        ChannelInterface $channel,
        string $email,
        string $locale,
        array $parameters = [],
    ): string;
}
