<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Command;

use Psr\Log\LoggerAwareInterface;
use Setono\SyliusAbandonedCartPlugin\Creator\NotificationCreatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    'setono:sylius-abandoned-cart:create-notifications',
    'Create notifications for idle carts that don\'t have a notification yet',
)]
final class CreateNotificationsCommand extends Command
{
    public function __construct(private readonly NotificationCreatorInterface $notificationCreator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Show what would be created without persisting',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dryRun = (bool) $input->getOption('dry-run');

        if ($this->notificationCreator instanceof LoggerAwareInterface && $output->isDebug()) {
            $this->notificationCreator->setLogger(new ConsoleLogger($output));
        }

        $count = $this->notificationCreator->create($dryRun);

        if ($dryRun) {
            $io->note(sprintf('[DRY-RUN] Would create %d notification(s)', $count));
        } else {
            $io->success(sprintf('Created %d notification(s)', $count));
        }

        return self::SUCCESS;
    }
}
