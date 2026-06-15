<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Pruner\Pruner;
use Setono\SyliusAbandonedCartPlugin\Pruner\PrunerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(Pruner::class)
        ->args([
            service('setono_sylius_abandoned_cart.repository.notification'),
            param('setono_sylius_abandoned_cart.prune_older_than'),
        ])
    ;

    $services->alias(PrunerInterface::class, Pruner::class);
};
