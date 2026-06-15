<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusAbandonedCartPlugin\EventSubscriber\Workflow\ResetProcessingErrorsSubscriber;
use Setono\SyliusAbandonedCartPlugin\EventSubscriber\Workflow\SetSentAtSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ResetProcessingErrorsSubscriber::class)
        ->tag('kernel.event_subscriber')
    ;

    $services->set(SetSentAtSubscriber::class)
        ->tag('kernel.event_subscriber')
    ;
};
