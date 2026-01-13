<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EventSubscriber\Workflow;

use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Webmozart\Assert\Assert;

final class ResetProcessingErrorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        $event = sprintf(
            'workflow.%s.transition.%s',
            NotificationWorkflow::NAME,
            NotificationWorkflow::TRANSITION_PROCESS,
        );

        return [
            $event => 'reset',
        ];
    }

    public function reset(Event $event): void
    {
        /** @var object|NotificationInterface $notification */
        $notification = $event->getSubject();
        Assert::isInstanceOf($notification, NotificationInterface::class);

        $notification->resetProcessingErrors();
    }
}
