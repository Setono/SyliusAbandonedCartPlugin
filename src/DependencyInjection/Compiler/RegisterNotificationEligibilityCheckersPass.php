<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterNotificationEligibilityCheckersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('setono_sylius_abandoned_cart.notification_eligibility_checker.composite')) {
            return;
        }

        $composite = $container->getDefinition('setono_sylius_abandoned_cart.notification_eligibility_checker.composite');

        /** @var string $id */
        foreach (array_keys($container->findTaggedServiceIds('setono_sylius_abandoned_cart.notification_eligibility_checker')) as $id) {
            $composite->addMethodCall('add', [new Reference($id)]);
        }
    }
}
