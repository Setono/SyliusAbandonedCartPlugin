<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Pruner;

interface PrunerInterface
{
    /**
     * Will prune notifications table
     */
    public function prune(): void;
}
