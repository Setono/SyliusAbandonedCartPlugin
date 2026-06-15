<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Setono\CompositeCompilerPass\CompositeCompilerPass;
use Setono\SyliusAbandonedCartPlugin\SetonoSyliusAbandonedCartPlugin;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SetonoSyliusAbandonedCartPluginTest extends TestCase
{
    #[Test]
    public function it_resolves_its_path_to_the_repository_root(): void
    {
        // The plugin keeps its resources (config/, templates/, translations/) at the repository
        // root, so getPath() must point one level above src/.
        self::assertSame(\dirname(__DIR__, 2), (new SetonoSyliusAbandonedCartPlugin())->getPath());
    }

    #[Test]
    public function it_supports_the_doctrine_orm_driver(): void
    {
        self::assertSame(
            [SyliusResourceBundle::DRIVER_DOCTRINE_ORM],
            (new SetonoSyliusAbandonedCartPlugin())->getSupportedDrivers(),
        );
    }

    #[Test]
    public function it_registers_the_composite_eligibility_checker_compiler_pass(): void
    {
        $container = new ContainerBuilder();

        (new SetonoSyliusAbandonedCartPlugin())->build($container);

        $hasCompositePass = false;
        foreach ($container->getCompilerPassConfig()->getBeforeOptimizationPasses() as $pass) {
            if ($pass instanceof CompositeCompilerPass) {
                $hasCompositePass = true;

                break;
            }
        }

        self::assertTrue($hasCompositePass, 'Expected the CompositeCompilerPass to be registered');
    }

    #[Test]
    public function it_points_doctrine_mapping_discovery_at_the_model_directory(): void
    {
        $plugin = new SetonoSyliusAbandonedCartPlugin();

        $path = (new ReflectionMethod($plugin, 'getConfigFilesPath'))->invoke($plugin);

        self::assertSame($plugin->getPath() . '/config/doctrine/model', $path);
    }
}
