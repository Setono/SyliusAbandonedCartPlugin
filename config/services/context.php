<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Context\TokenValueBasedCartContext;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(TokenValueBasedCartContext::class)
        ->args([
            service('request_stack'),
            service('sylius.repository.order'),
        ])
        ->tag('sylius.context.cart', ['priority' => 100])
    ;
};
