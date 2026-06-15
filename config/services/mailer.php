<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManager;
use Setono\SyliusAbandonedCartPlugin\Mailer\EmailManagerInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGeneratorInterface;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGeneratorInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(EmailManager::class)
        ->args([
            service('sylius.email_sender'),
            service(CartRecoveryUrlGeneratorInterface::class),
            service(UnsubscribeUrlGeneratorInterface::class),
        ])
    ;

    $services->alias(EmailManagerInterface::class, EmailManager::class);
};
