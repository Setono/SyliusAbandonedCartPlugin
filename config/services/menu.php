<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Menu\AdminMenuListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(AdminMenuListener::class)
        ->tag('kernel.event_listener', [
            'event' => 'sylius.menu.admin.main',
            'method' => 'addAdminMenuItems',
        ])
    ;
};
