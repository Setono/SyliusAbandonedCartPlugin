<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\DependencyInjection;

use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

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
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $container->setParameter('setono_sylius_abandoned_cart.salt', $config['salt']);
        $container->setParameter('setono_sylius_abandoned_cart.idle_threshold', $config['idle_threshold']);
        $container->setParameter('setono_sylius_abandoned_cart.lookback_window', $config['lookback_window']);
        $container->setParameter('setono_sylius_abandoned_cart.prune_older_than', $config['prune_older_than']);

        $this->registerResources('setono_sylius_abandoned_cart', $config['driver'], $config['resources'], $container);

        $loader->load('services.php');

        $eligibilityCheckers = $config['eligibility_checkers'];
        if ($eligibilityCheckers['unsubscribed_customer']) {
            $loader->load('services/conditional/unsubscribed_customer.php');
        }

        if ($eligibilityCheckers['subscribed_to_newsletter']) {
            $loader->load('services/conditional/subscribed_to_newsletter.php');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'workflows' => NotificationWorkflow::getConfig(),
        ]);

        $container->prependExtensionConfig('sylius_grid', [
            'templates' => [
                'action' => [
                    'unsubscribed_customers' => '@SetonoSyliusAbandonedCartPlugin/admin/grid/action/unsubscribed_customers.html.twig',
                ],
            ],
            'grids' => [
                'setono_sylius_abandoned_cart_admin_notification' => [
                    'driver' => [
                        'name' => 'doctrine/orm',
                        'options' => [
                            'class' => '%setono_sylius_abandoned_cart.model.notification.class%',
                        ],
                    ],
                    'limits' => [100, 250, 500, 1000],
                    'fields' => [
                        'order' => [
                            'type' => 'twig',
                            'label' => 'sylius.ui.order',
                            'path' => 'cart',
                            'options' => [
                                'template' => '@SetonoSyliusAbandonedCartPlugin/admin/grid/label/cart.html.twig',
                            ],
                        ],
                        'channel' => [
                            'type' => 'twig',
                            'label' => 'sylius.ui.channel',
                            'path' => 'cart.channel',
                            'options' => [
                                'template' => '@SyliusAdmin/shared/grid/field/channel.html.twig',
                            ],
                        ],
                        'email' => [
                            'type' => 'string',
                            'label' => 'sylius.ui.customer',
                        ],
                        'state' => [
                            'type' => 'twig',
                            'label' => 'setono_sylius_abandoned_cart.ui.state',
                            'path' => '.',
                            'options' => [
                                'template' => '@SetonoSyliusAbandonedCartPlugin/admin/grid/label/notification_state.html.twig',
                            ],
                        ],
                        'createdAt' => [
                            'type' => 'datetime',
                            'label' => 'sylius.ui.created_at',
                            'sortable' => null,
                            'options' => [
                                'format' => 'd-m-Y H:i',
                            ],
                        ],
                        'sentAt' => [
                            'type' => 'datetime',
                            'label' => 'setono_sylius_abandoned_cart.ui.sent_at',
                            'sortable' => null,
                            'options' => [
                                'format' => 'd-m-Y H:i',
                            ],
                        ],
                        'lastClickedAt' => [
                            'type' => 'datetime',
                            'label' => 'setono_sylius_abandoned_cart.ui.last_clicked_at',
                            'sortable' => null,
                            'options' => [
                                'format' => 'd-m-Y H:i',
                            ],
                        ],
                        'revenue' => [
                            'type' => 'twig',
                            'label' => 'setono_sylius_abandoned_cart.ui.revenue',
                            'path' => '.',
                            'options' => [
                                'template' => '@SetonoSyliusAbandonedCartPlugin/admin/grid/label/revenue.html.twig',
                            ],
                        ],
                    ],
                    'actions' => [
                        'main' => [
                            'unsubscribed_customers' => [
                                'type' => 'unsubscribed_customers',
                            ],
                        ],
                    ],
                ],
                'setono_sylius_abandoned_cart_admin_unsubscribed_customer' => [
                    'driver' => [
                        'name' => 'doctrine/orm',
                        'options' => [
                            'class' => '%setono_sylius_abandoned_cart.model.unsubscribed_customer.class%',
                        ],
                    ],
                    'limits' => [100, 250, 500, 1000],
                    'fields' => [
                        'email' => [
                            'type' => 'string',
                            'label' => 'sylius.ui.customer',
                        ],
                        'createdAt' => [
                            'type' => 'datetime',
                            'label' => 'sylius.ui.created_at',
                            'sortable' => null,
                            'options' => [
                                'format' => 'd-m-Y H:i',
                            ],
                        ],
                    ],
                    'actions' => [
                        'main' => [
                            'create' => [
                                'type' => 'create',
                            ],
                        ],
                        'item' => [
                            'update' => [
                                'type' => 'update',
                            ],
                            'delete' => [
                                'type' => 'delete',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $container->prependExtensionConfig('sylius_mailer', [
            'emails' => [
                'abandoned_cart_email' => [
                    'template' => '@SetonoSyliusAbandonedCartPlugin/email/notification.html.twig',
                ],
            ],
        ]);
    }
}
