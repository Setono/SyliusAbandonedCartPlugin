<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Command;

use Psr\Log\LoggerAwareInterface;
use Setono\SyliusAbandonedCartPlugin\Processor\NotificationProcessorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('setono:sylius-abandoned-cart:process-notifications', 'Process pending notifications')]
final class ProcessNotificationsCommand extends Command
{
    public function __construct(private readonly NotificationProcessorInterface $notificationProcessor)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->notificationProcessor instanceof LoggerAwareInterface && $output->isDebug()) {
            $this->notificationProcessor->setLogger(new ConsoleLogger($output));
        }

        $this->notificationProcessor->process();

        return self::SUCCESS;
    }
}
