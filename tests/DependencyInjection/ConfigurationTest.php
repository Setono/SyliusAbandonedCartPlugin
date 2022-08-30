<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusAbandonedCartPlugin\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\DependencyInjection\Configuration;

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
    public function values_are_invalid_if_required_value_is_not_provided(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [], // no values at all
            ],
            '/The child (config|node) "option" (under|at path) "setono_sylius_abandoned_cart" must be configured/',
            true
        );
    }

    /**
     * @test
     */
    public function processed_value_contains_required_value(): void
    {
        $this->assertProcessedConfigurationEquals([
            ['option' => 'first value'],
            ['option' => 'last value'],
        ], [
            'option' => 'last value',
        ]);
    }
}
