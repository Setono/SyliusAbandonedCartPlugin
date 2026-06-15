<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->import(__DIR__ . '/services/command.php');
    $container->import(__DIR__ . '/services/context.php');
    $container->import(__DIR__ . '/services/controller.php');
    $container->import(__DIR__ . '/services/creator.php');
    $container->import(__DIR__ . '/services/data_provider.php');
    $container->import(__DIR__ . '/services/eligibility_checker.php');
    $container->import(__DIR__ . '/services/event_subscriber.php');
    $container->import(__DIR__ . '/services/factory.php');
    $container->import(__DIR__ . '/services/form.php');
    $container->import(__DIR__ . '/services/hasher.php');
    $container->import(__DIR__ . '/services/mailer.php');
    $container->import(__DIR__ . '/services/menu.php');
    $container->import(__DIR__ . '/services/processor.php');
    $container->import(__DIR__ . '/services/pruner.php');
    $container->import(__DIR__ . '/services/url_generator.php');
};
