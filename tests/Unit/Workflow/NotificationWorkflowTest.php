<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Workflow;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;

final class NotificationWorkflowTest extends TestCase
{
    #[Test]
    public function it_returns_all_states(): void
    {
        self::assertSame(
            ['failed', 'ineligible', 'pending', 'processing', 'sent'],
            NotificationWorkflow::getStates(),
        );
    }

    #[Test]
    public function it_builds_a_state_machine_config_keyed_by_its_name(): void
    {
        $config = NotificationWorkflow::getConfig();

        self::assertArrayHasKey(NotificationWorkflow::NAME, $config);

        $workflow = $config[NotificationWorkflow::NAME];
        self::assertIsArray($workflow);

        self::assertSame('state_machine', $workflow['type']);
        self::assertSame(NotificationWorkflow::STATE_PENDING, $workflow['initial_marking']);
        self::assertSame(NotificationInterface::class, $workflow['supports']);
        self::assertSame(NotificationWorkflow::getStates(), $workflow['places']);
    }

    #[Test]
    public function it_maps_every_transition_into_the_config(): void
    {
        $workflow = NotificationWorkflow::getConfig()[NotificationWorkflow::NAME];
        self::assertIsArray($workflow);

        $transitions = $workflow['transitions'];
        self::assertIsArray($transitions);

        $send = $transitions[NotificationWorkflow::TRANSITION_SEND];
        self::assertIsArray($send);
        self::assertSame([NotificationWorkflow::STATE_PROCESSING], $send['from']);
        self::assertSame([NotificationWorkflow::STATE_SENT], $send['to']);

        // The fail transition can be applied from any state.
        $fail = $transitions[NotificationWorkflow::TRANSITION_FAIL];
        self::assertIsArray($fail);
        self::assertSame(NotificationWorkflow::getStates(), $fail['from']);
    }

    #[Test]
    public function it_defines_four_transitions(): void
    {
        self::assertCount(4, NotificationWorkflow::getTransitions());
    }
}
