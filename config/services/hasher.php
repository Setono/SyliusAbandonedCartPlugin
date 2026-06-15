<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasher;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(EmailHasher::class)
        ->args([
            param('setono_sylius_abandoned_cart.salt'),
        ])
    ;

    $services->alias(EmailHasherInterface::class, EmailHasher::class);
};
