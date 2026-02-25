<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\EventSubscriber\Workflow;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\EventSubscriber\Workflow\SetSentAtSubscriber;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

final class SetSentAtSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_sets_sent_at_on_send_completion(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->setSentAt(Argument::type(\DateTimeImmutable::class))->shouldBeCalled();

        $event = new Event(
            $notification->reveal(),
            new Marking([NotificationWorkflow::STATE_SENT => 1]),
            new Transition(NotificationWorkflow::TRANSITION_SEND, NotificationWorkflow::STATE_PROCESSING, NotificationWorkflow::STATE_SENT),
        );

        $subscriber = new SetSentAtSubscriber();
        $subscriber->set($event);
    }

    /**
     * @test
     */
    public function it_subscribes_to_send_completed_event(): void
    {
        $events = SetSentAtSubscriber::getSubscribedEvents();

        $expectedEvent = sprintf(
            'workflow.%s.completed.%s',
            NotificationWorkflow::NAME,
            NotificationWorkflow::TRANSITION_SEND,
        );

        self::assertArrayHasKey($expectedEvent, $events);
        self::assertSame('set', $events[$expectedEvent]);
    }
}
