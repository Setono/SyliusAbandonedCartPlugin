<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGenerator;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGeneratorInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGenerator;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGeneratorInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CartRecoveryUrlGenerator::class)
        ->args([
            service('router'),
            'setono_sylius_abandoned_cart_shop_recover_cart',
        ])
    ;

    $services->alias(CartRecoveryUrlGeneratorInterface::class, CartRecoveryUrlGenerator::class);

    $services->set(UnsubscribeUrlGenerator::class)
        ->args([
            service('router'),
            service(EmailHasherInterface::class),
            'setono_sylius_abandoned_cart_shop_unsubscribe_customer',
        ])
    ;

    $services->alias(UnsubscribeUrlGeneratorInterface::class, UnsubscribeUrlGenerator::class);
};
