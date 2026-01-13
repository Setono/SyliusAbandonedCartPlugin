<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Processor;

interface NotificationProcessorInterface
{
    /**
     * Processes all pending notifications.
     */
    public function process(): void;
}
