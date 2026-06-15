<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Creator\NotificationCreator;
use Setono\SyliusAbandonedCartPlugin\Creator\NotificationCreatorInterface;
use Setono\SyliusAbandonedCartPlugin\DataProvider\IdleCartDataProviderInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(NotificationCreator::class)
        ->args([
            service('doctrine'),
            service(IdleCartDataProviderInterface::class),
            service('setono_sylius_abandoned_cart.factory.notification'),
        ])
    ;

    $services->alias(NotificationCreatorInterface::class, NotificationCreator::class);
};
