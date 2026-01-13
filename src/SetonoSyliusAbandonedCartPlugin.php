<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin;

use Setono\CompositeCompilerPass\CompositeCompilerPass;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\CompositeNotificationEligibilityChecker;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Sylius\Bundle\ResourceBundle\AbstractResourceBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SetonoSyliusAbandonedCartPlugin extends AbstractResourceBundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CompositeCompilerPass(
            CompositeNotificationEligibilityChecker::class,
            'setono_sylius_abandoned_cart.notification_eligibility_checker',
        ));
    }

    /** @return list<string> */
    public function getSupportedDrivers(): array
    {
        return [
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }
}
