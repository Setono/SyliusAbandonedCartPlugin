<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Command\CreateNotificationsCommand;
use Setono\SyliusAbandonedCartPlugin\Creator\NotificationCreatorInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateNotificationsCommandTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_creates_notifications(): void
    {
        $creator = $this->prophesize(NotificationCreatorInterface::class);
        $creator->create(false)->willReturn(5)->shouldBeCalled();

        $commandTester = new CommandTester(new CreateNotificationsCommand($creator->reveal()));
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('Created 5 notification(s)', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_supports_dry_run_option(): void
    {
        $creator = $this->prophesize(NotificationCreatorInterface::class);
        $creator->create(true)->willReturn(3)->shouldBeCalled();

        $commandTester = new CommandTester(new CreateNotificationsCommand($creator->reveal()));
        $commandTester->execute(['--dry-run' => true]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('[DRY-RUN]', $commandTester->getDisplay());
        self::assertStringContainsString('3 notification(s)', $commandTester->getDisplay());
    }
}
