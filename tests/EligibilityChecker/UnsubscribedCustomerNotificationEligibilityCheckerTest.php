<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\UnsubscribedCustomerNotificationEligibilityChecker;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Repository\UnsubscribedCustomerRepositoryInterface;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\EligibilityChecker\UnsubscribedCustomerNotificationEligibilityChecker
 */
final class UnsubscribedCustomerNotificationEligibilityCheckerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_returns_eligible_when_email_is_null(): void
    {
        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);

        $checker = new UnsubscribedCustomerNotificationEligibilityChecker($repository->reveal());
        self::assertTrue($checker->check(new Notification())->eligible);
    }

    /**
     * @test
     */
    public function it_returns_eligible_when_email_is_not_unsubscribed(): void
    {
        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $repository->isUnsubscribed('johndoe@example.com')->willReturn(false);

        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getEmail()->willReturn('johndoe@example.com');

        $checker = new UnsubscribedCustomerNotificationEligibilityChecker($repository->reveal());
        self::assertTrue($checker->check($notification->reveal())->eligible);
    }

    /**
     * @test
     */
    public function it_returns_non_eligible_when_email_is_unsubscribed(): void
    {
        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $repository->isUnsubscribed('johndoe@example.com')->willReturn(true);

        $notification = $this->prophesize(NotificationInterface::class);
        $notification->getEmail()->willReturn('johndoe@example.com');

        $checker = new UnsubscribedCustomerNotificationEligibilityChecker($repository->reveal());
        self::assertFalse($checker->check($notification->reveal())->eligible);
    }
}
