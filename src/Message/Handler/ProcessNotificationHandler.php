<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Message\Handler;

use Setono\SyliusAbandonedCartPlugin\Message\Command\ProcessNotification;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Processor\NotificationProcessorInterface;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class ProcessNotificationHandler implements MessageHandlerInterface
{
    private NotificationRepositoryInterface $notificationRepository;

    private NotificationProcessorInterface $notificationProcessor;

    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        NotificationProcessorInterface $notificationProcessor,
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->notificationProcessor = $notificationProcessor;
    }

    public function __invoke(ProcessNotification $message): void
    {
        /** @var NotificationInterface|object|null $notification */
        $notification = $this->notificationRepository->find($message->notificationId);
        Assert::nullOrIsInstanceOf($notification, NotificationInterface::class);

        if (null === $notification) {
            throw new UnrecoverableMessageHandlingException(sprintf(
                'Could not find notification with id %d',
                $message->notificationId,
            ));
        }

        try {
            $this->notificationProcessor->process($notification);
        } catch (Throwable $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage(), 0, $e);
        }
    }
}
