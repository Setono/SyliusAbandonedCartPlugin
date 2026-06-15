<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Form\Type\UnsubscribedCustomerType;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('setono_sylius_abandoned_cart.form.type.unsubscribed_customer.validation_groups', ['setono_sylius_abandoned_cart'])
    ;

    $services = $container->services();

    $services->set(UnsubscribedCustomerType::class)
        ->args([
            param('setono_sylius_abandoned_cart.model.unsubscribed_customer.class'),
            param('setono_sylius_abandoned_cart.form.type.unsubscribed_customer.validation_groups'),
        ])
        ->tag('form.type')
    ;
};
