<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin;

use Setono\SyliusAbandonedCartPlugin\DependencyInjection\Compiler\RegisterNotificationEligibilityCheckersPass;
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

        $container->addCompilerPass(new RegisterNotificationEligibilityCheckersPass());
    }

    /** @return list<string> */
    public function getSupportedDrivers(): array
    {
        return [
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }
}
