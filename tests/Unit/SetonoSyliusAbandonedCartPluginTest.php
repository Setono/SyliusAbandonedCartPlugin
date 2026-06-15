<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\SetonoSyliusAbandonedCartPlugin;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;

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
}
