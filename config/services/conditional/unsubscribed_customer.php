<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\UnsubscribedCustomerNotificationEligibilityChecker;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(UnsubscribedCustomerNotificationEligibilityChecker::class)
        ->args([
            service('setono_sylius_abandoned_cart.repository.unsubscribed_customer'),
        ])
        ->tag('setono_sylius_abandoned_cart.notification_eligibility_checker')
    ;
};
