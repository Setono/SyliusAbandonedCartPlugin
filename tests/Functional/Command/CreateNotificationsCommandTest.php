<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Functional\Command;

use Setono\SyliusAbandonedCartPlugin\Tests\Application\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateNotificationsCommandTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * @test
     */
    public function it_creates_notifications(): void
    {
        $application = new Application(self::bootKernel());

        $command = $application->find('setono:sylius-abandoned-cart:create-notifications');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('notification(s)', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function it_supports_dry_run_option(): void
    {
        $application = new Application(self::bootKernel());

        $command = $application->find('setono:sylius-abandoned-cart:create-notifications');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--dry-run' => true]);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('[DRY-RUN]', $commandTester->getDisplay());
        self::assertStringContainsString('notification(s)', $commandTester->getDisplay());
    }
}
