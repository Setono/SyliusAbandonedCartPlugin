<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\CompositeNotificationEligibilityChecker;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\OrderHasItemsNotificationEligibilityChecker;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(CompositeNotificationEligibilityChecker::class);

    $services->alias(NotificationEligibilityCheckerInterface::class, CompositeNotificationEligibilityChecker::class);

    $services->set(OrderHasItemsNotificationEligibilityChecker::class)
        ->tag('setono_sylius_abandoned_cart.notification_eligibility_checker')
    ;
};
