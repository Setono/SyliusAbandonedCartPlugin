<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Hasher;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasher;

#[CoversClass(EmailHasher::class)]
final class EmailHasherTest extends TestCase
{
    #[Test]
    public function it_hashes(): void
    {
        $hasher = new EmailHasher('salt');

        self::assertSame('2ac21379842b5445001475a596caab5843ecbc6be46c27f882cb6c0bd75fb9f9', $hasher->hash('johndoe@example.com'));
    }
}
