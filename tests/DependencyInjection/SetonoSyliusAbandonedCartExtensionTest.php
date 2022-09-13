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
    }
}
