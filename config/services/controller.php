<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Controller\Action\RecoverCartAction;
use Setono\SyliusAbandonedCartPlugin\Controller\Action\UnsubscribeCustomerAction;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()->public();

    $services->set(UnsubscribeCustomerAction::class)
        ->args([
            service(EmailHasherInterface::class),
            service('setono_sylius_abandoned_cart.repository.unsubscribed_customer'),
            service('setono_sylius_abandoned_cart.factory.unsubscribed_customer'),
            service('twig'),
        ])
        ->tag('controller.service_arguments')
    ;

    $services->set(RecoverCartAction::class)
        ->args([
            service('sylius.repository.order'),
            service('setono_sylius_abandoned_cart.repository.notification'),
            service('router'),
            service('request_stack'),
            service('doctrine'),
        ])
        ->tag('controller.service_arguments')
    ;
};
