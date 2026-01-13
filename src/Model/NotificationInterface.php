<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Model;

use DateTimeInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\VersionedInterface;

interface NotificationInterface extends ResourceInterface, TimestampableInterface, VersionedInterface
{
    public function getId(): ?int;

    public function getState(): string;

    public function setState(string $state): void;

    /**
     * This is the abandoned cart
     */
    public function getCart(): ?OrderInterface;

    public function setCart(OrderInterface $cart): void;

    /**
     * @return list<string>
     */
    public function getProcessingErrors(): array;

    public function resetProcessingErrors(): void;

    public function addProcessingError(string $processingError): void;

    /**
     * @param list<string> $processingErrors
     */
    public function addProcessingErrors(array $processingErrors): void;

    public function getSentAt(): ?DateTimeInterface;

    public function setSentAt(DateTimeInterface $sentAt): void;

    public function getLastClickedAt(): ?DateTimeInterface;

    public function setLastClickedAt(?DateTimeInterface $lastClickedAt): void;

    public function isFailed(): bool;

    public function isIneligible(): bool;

    /**
     * Returns true if the notification is deletable
     */
    public function isDeletable(): bool;

    public function getEmail(): ?string;

    public function getRecipientFirstName(): ?string;
}
