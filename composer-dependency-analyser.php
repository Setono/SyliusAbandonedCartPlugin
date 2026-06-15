<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->addPathToExclude(__DIR__ . '/tests')
    ->ignoreErrorsOnPackage('symfony/validator', [ErrorType::UNUSED_DEPENDENCY])
    // sylius/sylius (the monolith) is a dev dependency that ships the same classes as the split
    // sylius/* production packages, so the analyser misattributes their usage to it.
    ->ignoreErrorsOnPackage('sylius/sylius', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackages(
        ['sylius/channel', 'sylius/core', 'sylius/core-bundle', 'sylius/order', 'sylius/ui-bundle'],
        [ErrorType::UNUSED_DEPENDENCY],
    )
;
