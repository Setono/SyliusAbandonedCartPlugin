<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Command;

use Setono\SyliusAbandonedCartPlugin\Dispatcher\NotificationDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ProcessCommand extends Command
{
    protected static $defaultName = 'setono:sylius-abandoned-cart:process';

    private NotificationDispatcherInterface $notificationDispatcher;

    public function __construct(NotificationDispatcherInterface $notificationDispatcher)
    {
        parent::__construct();

        $this->notificationDispatcher = $notificationDispatcher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notificationDispatcher->dispatch();

        return 0;
    }
}
