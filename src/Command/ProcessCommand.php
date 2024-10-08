<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Command;

use Psr\Log\LoggerAwareInterface;
use Setono\SyliusAbandonedCartPlugin\Dispatcher\NotificationDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

final class ProcessCommand extends Command
{
    protected static $defaultName = 'setono:sylius-abandoned-cart:process';

    /** @var string|null */
    protected static $defaultDescription = 'Process pending notifications';

    public function __construct(private readonly NotificationDispatcherInterface $notificationDispatcher)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->notificationDispatcher instanceof LoggerAwareInterface && $output->isDebug()) {
            $this->notificationDispatcher->setLogger(new ConsoleLogger($output));
        }

        $this->notificationDispatcher->dispatch();

        return 0;
    }
}
