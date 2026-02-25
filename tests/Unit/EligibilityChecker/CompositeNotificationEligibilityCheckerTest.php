<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\EligibilityChecker;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\CompositeNotificationEligibilityChecker;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\EligibilityCheck;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;

final class CompositeNotificationEligibilityCheckerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_returns_eligible_when_all_checkers_pass(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);

        $checker1 = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $checker1->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $checker2 = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $checker2->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $composite = new CompositeNotificationEligibilityChecker();
        $composite->add($checker1->reveal());
        $composite->add($checker2->reveal());

        $result = $composite->check($notification->reveal());

        self::assertTrue($result->eligible);
        self::assertSame([], $result->reasons);
    }

    /**
     * @test
     */
    public function it_returns_ineligible_when_one_checker_fails(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);

        $checker1 = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $checker1->check($notification->reveal())->willReturn(new EligibilityCheck(true));

        $checker2 = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $checker2->check($notification->reveal())->willReturn(new EligibilityCheck(false, ['Customer unsubscribed']));

        $composite = new CompositeNotificationEligibilityChecker();
        $composite->add($checker1->reveal());
        $composite->add($checker2->reveal());

        $result = $composite->check($notification->reveal());

        self::assertFalse($result->eligible);
        self::assertSame(['Customer unsubscribed'], $result->reasons);
    }

    /**
     * @test
     */
    public function it_merges_reasons_when_multiple_checkers_fail(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);

        $checker1 = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $checker1->check($notification->reveal())->willReturn(new EligibilityCheck(false, ['Reason A']));

        $checker2 = $this->prophesize(NotificationEligibilityCheckerInterface::class);
        $checker2->check($notification->reveal())->willReturn(new EligibilityCheck(false, ['Reason B', 'Reason C']));

        $composite = new CompositeNotificationEligibilityChecker();
        $composite->add($checker1->reveal());
        $composite->add($checker2->reveal());

        $result = $composite->check($notification->reveal());

        self::assertFalse($result->eligible);
        self::assertSame(['Reason A', 'Reason B', 'Reason C'], $result->reasons);
    }

    /**
     * @test
     */
    public function it_returns_eligible_when_no_checkers_registered(): void
    {
        $notification = $this->prophesize(NotificationInterface::class);

        $composite = new CompositeNotificationEligibilityChecker();

        $result = $composite->check($notification->reveal());

        self::assertTrue($result->eligible);
        self::assertSame([], $result->reasons);
    }
}
