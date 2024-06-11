<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\Pruner;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Pruner\Pruner;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\Pruner\Pruner
 */
final class PrunerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_prunes(): void
    {
        $expected = (new \DateTimeImmutable())->sub(new \DateInterval('PT30M'));
        self::assertNotFalse($expected);

        $repository = $this->prophesize(NotificationRepositoryInterface::class);
        $repository->removeOlderThan(Argument::that(static function (\DateTimeInterface $dateTime) use ($expected) {
            // the reason we don't do an exact match between the two timestamps is that this test allows a second to pass
            // between call of the method and the instantiation of the new DateTime object
            return abs($dateTime->getTimestamp() - $expected->getTimestamp()) <= 1;
        }))->shouldBeCalled();

        $pruner = new Pruner($repository->reveal(), 30);
        $pruner->prune();
    }
}
