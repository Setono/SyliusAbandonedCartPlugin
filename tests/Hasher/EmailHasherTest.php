<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\Hasher;

use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasher;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasher
 */
final class EmailHasherTest extends TestCase
{
    /**
     * @test
     */
    public function it_hashes(): void
    {
        $hasher = new EmailHasher('salt');

        self::assertSame('2ac21379842b5445001475a596caab5843ecbc6be46c27f882cb6c0bd75fb9f9', $hasher->hash('johndoe@example.com'));
    }
}
