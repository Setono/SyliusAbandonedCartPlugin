<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\EventSubscriber\Workflow;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\EventSubscriber\Workflow\ResetProcessingErrorsSubscriber;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

final class ResetProcessingErrorsSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_resets_errors_on_process_transition(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);
        $notification->resetProcessingErrors()->shouldBeCalled();

        $event = new Event(
            $notification->reveal(),
            new Marking([NotificationWorkflow::STATE_PENDING => 1]),
            new Transition(NotificationWorkflow::TRANSITION_PROCESS, NotificationWorkflow::STATE_PENDING, NotificationWorkflow::STATE_PROCESSING),
        );

        $subscriber = new ResetProcessingErrorsSubscriber();
        $subscriber->reset($event);
    }

    /**
     * @test
     */
    public function it_subscribes_to_process_transition_event(): void
    {
        $events = ResetProcessingErrorsSubscriber::getSubscribedEvents();

        $expectedEvent = sprintf(
            'workflow.%s.transition.%s',
            NotificationWorkflow::NAME,
            NotificationWorkflow::TRANSITION_PROCESS,
        );

        self::assertArrayHasKey($expectedEvent, $events);
        self::assertSame('reset', $events[$expectedEvent]);
    }
}
