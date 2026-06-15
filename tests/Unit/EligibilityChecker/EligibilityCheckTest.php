<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\EligibilityChecker;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\EligibilityCheck;

final class EligibilityCheckTest extends TestCase
{
    #[Test]
    public function it_is_eligible_with_no_reasons_by_default(): void
    {
        $check = new EligibilityCheck(true);

        self::assertTrue($check->eligible);
        self::assertSame([], $check->reasons);
    }

    #[Test]
    public function it_normalizes_a_single_string_reason_into_a_list(): void
    {
        $check = new EligibilityCheck(false, 'customer unsubscribed');

        self::assertFalse($check->eligible);
        self::assertSame(['customer unsubscribed'], $check->reasons);
    }

    #[Test]
    public function it_keeps_a_list_of_reasons(): void
    {
        $check = new EligibilityCheck(false, ['no items', 'customer unsubscribed']);

        self::assertSame(['no items', 'customer unsubscribed'], $check->reasons);
    }
}
