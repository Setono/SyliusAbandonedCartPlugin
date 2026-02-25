<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Functional\Command;

use Setono\SyliusAbandonedCartPlugin\Tests\Application\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class PruneNotificationsCommandTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * @test
     */
    public function it_prunes(): void
    {
        $application = new Application(self::bootKernel());

        $command = $application->find('setono:sylius-abandoned-cart:prune-notifications');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
    }
}
