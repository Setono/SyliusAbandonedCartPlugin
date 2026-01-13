<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Command;

use Setono\SyliusAbandonedCartPlugin\Pruner\PrunerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('setono:sylius-abandoned-cart:prune-notifications', 'Prune older notifications')]
final class PruneNotificationsCommand extends Command
{
    public function __construct(private readonly PrunerInterface $pruner)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pruner->prune();

        return self::SUCCESS;
    }
}
