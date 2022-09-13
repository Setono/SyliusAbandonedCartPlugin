<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Model;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface UnsubscribedCustomerInterface extends ResourceInterface, TimestampableInterface
{
    public function getId(): ?int;

    public function getEmail(): ?string;

    public function setEmail(string $email): void;
}
