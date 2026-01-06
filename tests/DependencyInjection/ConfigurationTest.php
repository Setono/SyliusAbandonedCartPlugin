<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\DependencyInjection\Configuration;
use Setono\SyliusAbandonedCartPlugin\Form\Type\UnsubscribedCustomerType;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomer;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepository;
use Setono\SyliusAbandonedCartPlugin\Repository\UnsubscribedCustomerRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Form\Type\DefaultResourceType;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Component\Resource\Factory\Factory;

/**
 * See examples of tests and configuration options here: https://github.com/SymfonyTest/SymfonyConfigTest
 */
final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    /**
     * @test
     */
    public function processed_value_contains_required_value(): void
    {
        $this->assertProcessedConfigurationEquals([], [
            'driver' => SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
            'salt' => 's3cr3t',
            'idle_threshold' => 60,
            'prune_older_than' => 43_200,
            'eligibility_checkers' => [
                'unsubscribed_customer' => true,
                'subscribed_to_newsletter' => false,
            ],
            'resources' => [
                'notification' => [
                    'classes' => [
                        'model' => Notification::class,
                        'controller' => ResourceController::class,
                        'repository' => NotificationRepository::class,
                        'form' => DefaultResourceType::class,
                        'factory' => Factory::class,
                    ],
                ],
                'unsubscribed_customer' => [
                    'classes' => [
                        'model' => UnsubscribedCustomer::class,
                        'controller' => ResourceController::class,
                        'repository' => UnsubscribedCustomerRepository::class,
                        'form' => UnsubscribedCustomerType::class,
                        'factory' => Factory::class,
                    ],
                ],
            ],
        ]);
    }
}
