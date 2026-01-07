<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DependencyInjection;

use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusAbandonedCartExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         *
         * @var array{driver: string, salt: string, idle_threshold: int, lookback_window: int, prune_older_than: int, eligibility_checkers: array{unsubscribed_customer: bool, subscribed_to_newsletter: bool}, resources: array<string, mixed>} $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter('setono_sylius_abandoned_cart.salt', $config['salt']);
        $container->setParameter('setono_sylius_abandoned_cart.idle_threshold', $config['idle_threshold']);
        $container->setParameter('setono_sylius_abandoned_cart.lookback_window', $config['lookback_window']);
        $container->setParameter('setono_sylius_abandoned_cart.prune_older_than', $config['prune_older_than']);

        $this->registerResources('setono_sylius_abandoned_cart', $config['driver'], $config['resources'], $container);

        $loader->load('services.xml');

        $eligibilityCheckers = $config['eligibility_checkers'];
        if ($eligibilityCheckers['unsubscribed_customer']) {
            $loader->load('services/conditional/unsubscribed_customer.xml');
        }

        if ($eligibilityCheckers['subscribed_to_newsletter']) {
            $loader->load('services/conditional/subscribed_to_newsletter.xml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'workflows' => NotificationWorkflow::getConfig(),
        ]);
    }
}
