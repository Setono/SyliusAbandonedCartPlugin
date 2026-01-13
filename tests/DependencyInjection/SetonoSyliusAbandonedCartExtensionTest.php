<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusAbandonedCartPlugin\DependencyInjection\SetonoSyliusAbandonedCartExtension;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\SubscribedToNewsletterNotificationEligibilityChecker;
use Setono\SyliusAbandonedCartPlugin\EligibilityChecker\UnsubscribedCustomerNotificationEligibilityChecker;

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

        $this->assertContainerBuilderNotHasService(UnsubscribedCustomerNotificationEligibilityChecker::class);
        $this->assertContainerBuilderNotHasService(SubscribedToNewsletterNotificationEligibilityChecker::class);
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

        $this->assertContainerBuilderHasService(UnsubscribedCustomerNotificationEligibilityChecker::class);
        $this->assertContainerBuilderHasService(SubscribedToNewsletterNotificationEligibilityChecker::class);
    }
}
