<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Model;

use DateTimeInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class Notification implements NotificationInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected ?int $version = 1;

    protected ?string $state = null;

    protected ?OrderInterface $cart = null;

    /** @var list<string>|null */
    protected ?array $processingErrors = null;

    protected ?DateTimeInterface $sentAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): void
    {
        $this->version = $version;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getCart(): ?OrderInterface
    {
        return $this->cart;
    }

    public function setCart(?OrderInterface $cart): void
    {
        $this->cart = $cart;
    }

    public function getProcessingErrors(): array
    {
        return $this->processingErrors ?? [];
    }

    public function resetProcessingErrors(): void
    {
        $this->processingErrors = null;
    }

    public function addProcessingError(string $processingError): void
    {
        $this->processingErrors[] = $processingError;
    }

    public function addProcessingErrors(array $processingErrors): void
    {
        foreach ($processingErrors as $processingError) {
            $this->addProcessingError($processingError);
        }
    }

    public function getSentAt(): ?DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?DateTimeInterface $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function getEmail(): ?string
    {
        $cart = $this->getCart();
        if (null === $cart) {
            return null;
        }

        $customer = $cart->getCustomer();
        if (null === $customer) {
            return null;
        }

        return $customer->getEmailCanonical();
    }
}
