<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Functional\Command;

use Setono\SyliusAbandonedCartPlugin\Tests\Application\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ProcessNotificationsCommandTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * @test
     */
    public function it_executes_successfully_with_no_pending_notifications(): void
    {
        $application = new Application(self::bootKernel());

        $command = $application->find('setono:sylius-abandoned-cart:process-notifications');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }

    /**
     * @test
     */
    public function it_is_registered_and_findable(): void
    {
        $application = new Application(self::bootKernel());

        $command = $application->find('setono:sylius-abandoned-cart:process-notifications');

        self::assertSame('setono:sylius-abandoned-cart:process-notifications', $command->getName());
    }
}
