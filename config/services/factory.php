<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Factory\NotificationFactory;
use Setono\SyliusAbandonedCartPlugin\Factory\OrderFactory;
use Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactory;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(NotificationFactory::class)
        ->decorate('setono_sylius_abandoned_cart.factory.notification', null, 64)
        ->args([
            service(NotificationFactory::class . '.inner'),
        ])
    ;

    $services->set(UnsubscribedCustomerFactory::class)
        ->decorate('setono_sylius_abandoned_cart.factory.unsubscribed_customer', null, 64)
        ->args([
            service(UnsubscribedCustomerFactory::class . '.inner'),
        ])
    ;

    $services->set(OrderFactory::class)
        ->decorate('sylius.factory.order', null, 64)
        ->args([
            service(OrderFactory::class . '.inner'),
            service(OrderTokenAssignerInterface::class),
        ])
    ;
};
