<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusAbandonedCartPlugin\DependencyInjection\SetonoSyliusAbandonedCartExtension;

/**
 * See examples of tests and configuration options here: https://github.com/SymfonyTest/SymfonyDependencyInjectionTest
 */
final class SetonoSyliusAbandonedCartExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new SetonoSyliusAbandonedCartExtension(),
        ];
    }

    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('setono_sylius_abandoned_cart.salt', 's3cr3t');
        $this->assertContainerBuilderHasParameter('setono_sylius_abandoned_cart.idle_threshold', 60);
        $this->assertContainerBuilderHasParameter('setono_sylius_abandoned_cart.prune_older_than', 43_200);
    }

    /**
     * @test
     */
    public function it_does_not_load_eligibility_checkers(): void
    {
        $this->load([
            'eligibility_checkers' => [
                'unsubscribed_customer' => false,
                'subscribed_to_newsletter' => false,
            ],
        ]);

        $this->assertContainerBuilderNotHasService('setono_sylius_abandoned_cart.notification_eligibility_checker.unsubscribed_customer');
        $this->assertContainerBuilderNotHasService('setono_sylius_abandoned_cart.notification_eligibility_checker.subscribed_to_newsletter');
    }

    /**
     * @test
     */
    public function it_loads_eligibility_checkers(): void
    {
        $this->load([
            'eligibility_checkers' => [
                'unsubscribed_customer' => true,
                'subscribed_to_newsletter' => true,
            ],
        ]);

        $this->assertContainerBuilderHasService('setono_sylius_abandoned_cart.notification_eligibility_checker.unsubscribed_customer');
        $this->assertContainerBuilderHasService('setono_sylius_abandoned_cart.notification_eligibility_checker.subscribed_to_newsletter');
    }
}
