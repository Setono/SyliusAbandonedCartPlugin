<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EventSubscriber\Workflow;

use DateTimeImmutable;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Webmozart\Assert\Assert;

final class SetSentAtSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        $event = sprintf('workflow.%s.completed.%s', NotificationWorkflow::NAME, NotificationWorkflow::TRANSITION_SEND);

        return [
            $event => 'set',
        ];
    }

    public function set(Event $event): void
    {
        /** @var object|NotificationInterface $notification */
        $notification = $event->getSubject();
        Assert::isInstanceOf($notification, NotificationInterface::class);

        $notification->setSentAt(new DateTimeImmutable());
    }
}
