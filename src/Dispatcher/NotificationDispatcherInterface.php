<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Dispatcher;

interface NotificationDispatcherInterface
{
    /**
     * Will dispatch notifications for processing
     */
    public function dispatch(): void;
}
