<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\DataProvider\PendingNotificationDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\NotificationEligibilityCheckerInterface;
use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManagerInterface;
use Setono\SyliusAbandonedCartPlugin\Processor\NotificationProcessor;
use Setono\SyliusAbandonedCartPlugin\Processor\NotificationProcessorInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(NotificationProcessor::class)
        ->args([
            service('doctrine'),
            service(PendingNotificationDataProviderInterface::class),
            service(EmailManagerInterface::class),
            service('state_machine.setono_sylius_abandoned_cart_notification'),
            service(NotificationEligibilityCheckerInterface::class),
        ])
        ->call('setLogger', [service('logger')])
    ;

    $services->alias(NotificationProcessorInterface::class, NotificationProcessor::class);
};
