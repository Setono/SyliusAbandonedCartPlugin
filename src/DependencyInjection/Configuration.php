<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DependencyInjection;

use Setono\SyliusAbandonedCartPlugin\Form\Type\UnsubscribedCustomerType;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomer;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepository;
use Setono\SyliusAbandonedCartPlugin\Repository\UnsubscribedCustomerRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Form\Type\DefaultResourceType;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_abandoned_cart');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress MixedMethodCall,PossiblyNullReference,PossiblyUndefinedMethod,UndefinedInterfaceMethod */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('driver')
                    ->defaultValue(SyliusResourceBundle::DRIVER_DOCTRINE_ORM)
                ->end()
                ->scalarNode('salt')
                    ->defaultValue('s3cr3t')
                    ->info('When unsubscribing a customer a hash is used to prevent false unsubscribes. This hash is generated using this salt.')
                    ->cannotBeEmpty()
                ->end()
                ->integerNode('idle_threshold')
                    ->defaultValue(60)
                    ->info('The number of minutes before a cart is considered idle and could be considered for an abandoned cart notification')
                ->end()
                ->integerNode('prune_older_than')
                    ->info('Prune notifications that are older than this number of minutes. Default: 30 days (30 * 24 * 60)')
                    ->defaultValue(43_200) // 30 days
                ->end()
                ->arrayNode('eligibility_checkers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('unsubscribed_customer')
                            ->info('If this is true, you will not send notifications to customer who actively unsubscribed from a previous abandoned cart notification')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('subscribed_to_newsletter')
                            ->info('If this is true, you will not send notifications to customer who have not subscribed to your newsletter')
                            ->defaultFalse()
        ;

        $this->addResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        /** @psalm-suppress MixedMethodCall,PossiblyNullReference,PossiblyUndefinedMethod,UndefinedInterfaceMethod */
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('notification')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Notification::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(NotificationRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(DefaultResourceType::class)->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('unsubscribed_customer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(UnsubscribedCustomer::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(UnsubscribedCustomerRepository::class)->cannotBeEmpty()->end()
                                        ->scalarNode('form')->defaultValue(UnsubscribedCustomerType::class)->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->end()
        ;
    }
}
