<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Hasher;

interface EmailHasherInterface
{
    public function hash(string $email): string;
}
