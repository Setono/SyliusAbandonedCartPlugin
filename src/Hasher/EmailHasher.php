<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Hasher;

final class EmailHasher implements EmailHasherInterface
{
    private string $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    public function hash(string $email): string
    {
        return hash('sha256', $email . $this->salt);
    }
}
