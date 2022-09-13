<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Command\PruneCommand;
use Setono\SyliusAbandonedCartPlugin\Pruner\PrunerInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @test
 */
final class PruneCommandTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_prunes(): void
    {
        $pruner = $this->prophesize(PrunerInterface::class);
        $pruner->prune()->shouldBeCalled();

        $commandTester = new CommandTester(new PruneCommand($pruner->reveal()));
        $commandTester->execute([]);

        self::assertSame(0, $commandTester->getStatusCode());
    }
}
