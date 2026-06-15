<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\DataProvider\IdleCartDataProvider;
use Setono\SyliusAbandonedCartPlugin\DataProvider\IdleCartDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\DataProvider\PendingNotificationDataProvider;
use Setono\SyliusAbandonedCartPlugin\DataProvider\PendingNotificationDataProviderInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(IdleCartDataProvider::class)
        ->args([
            service('doctrine'),
            service('event_dispatcher'),
            param('sylius.model.order.class'),
            param('setono_sylius_abandoned_cart.model.notification.class'),
            param('setono_sylius_abandoned_cart.idle_threshold'),
            param('setono_sylius_abandoned_cart.lookback_window'),
        ])
    ;

    $services->alias(IdleCartDataProviderInterface::class, IdleCartDataProvider::class);

    $services->set(PendingNotificationDataProvider::class)
        ->args([
            service('doctrine'),
            param('setono_sylius_abandoned_cart.model.notification.class'),
            param('setono_sylius_abandoned_cart.idle_threshold'),
        ])
    ;

    $services->alias(PendingNotificationDataProviderInterface::class, PendingNotificationDataProvider::class);
};
