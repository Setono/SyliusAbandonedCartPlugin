<?php

declare(strict_types=1);

use Sylius\Bundle\ApiBundle\SyliusApiBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    // Resolve the Sylius API resources directory from the bundle itself so it works whether the
    // sylius/sylius monorepo or the split sylius/api-bundle package is installed (CI removes the
    // monorepo for the static-code-analysis / dependency-analysis steps).
    $apiBundleDir = \dirname((string) (new \ReflectionClass(SyliusApiBundle::class))->getFileName());

    $container->extension('api_platform', [
        'mapping' => [
            'paths' => [
                $apiBundleDir . '/Resources/config/api_platform',
                '%kernel.project_dir%/config/api_platform',
                '%kernel.project_dir%/src/Entity',
            ],
        ],
        'patch_formats' => [
            'json' => ['application/merge-patch+json'],
        ],
        'swagger' => [
            'versions' => [3],
        ],
    ]);
};
