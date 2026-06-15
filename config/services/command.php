<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Command\CreateNotificationsCommand;
use Setono\SyliusAbandonedCartPlugin\Command\ProcessNotificationsCommand;
use Setono\SyliusAbandonedCartPlugin\Command\PruneNotificationsCommand;
use Setono\SyliusAbandonedCartPlugin\Creator\NotificationCreatorInterface;
use Setono\SyliusAbandonedCartPlugin\Processor\NotificationProcessorInterface;
use Setono\SyliusAbandonedCartPlugin\Pruner\PrunerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ProcessNotificationsCommand::class)
        ->args([service(NotificationProcessorInterface::class)])
        ->tag('console.command')
    ;

    $services->set(PruneNotificationsCommand::class)
        ->args([service(PrunerInterface::class)])
        ->tag('console.command')
    ;

    $services->set(CreateNotificationsCommand::class)
        ->args([service(NotificationCreatorInterface::class)])
        ->tag('console.command')
    ;
};
