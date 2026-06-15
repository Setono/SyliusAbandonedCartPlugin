<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\SubscribedToNewsletterNotificationEligibilityChecker;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(SubscribedToNewsletterNotificationEligibilityChecker::class)
        ->tag('setono_sylius_abandoned_cart.notification_eligibility_checker')
    ;
};
