<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Processor;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

interface NotificationProcessorInterface
{
    public function process(NotificationInterface $notification): void;
}
