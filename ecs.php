<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    $config->import('vendor/sylius-labs/coding-standard/ecs.php');
    $config->paths([
        'src',
        'tests',
        'composer-dependency-analyser.php',
        'ecs.php',
        'rector.php',
    ]);
    $config->skip([
        'tests/Application/node_modules/**',
        'tests/Application/var/**',
        // Auto-generated Symfony config reference (gitignored, rebuilt on cache warmup)
        'tests/Application/config/reference.php',
    ]);
};
