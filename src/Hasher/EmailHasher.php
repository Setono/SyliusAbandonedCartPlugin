<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Hasher;

final readonly class EmailHasher implements EmailHasherInterface
{
    public function __construct(private string $salt)
    {
    }

    public function hash(string $email): string
    {
        return hash('sha256', strtolower($email) . $this->salt);
    }
}
