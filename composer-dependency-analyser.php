<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

// The CI dependency-analysis step removes sylius/sylius first, so the split sylius/* packages are
// correctly detected as used in src/. symfony/validator is only used via XML validation config
// (config/validation), so it is genuinely undetectable in PHP and must be ignored.
return (new Configuration())
    ->addPathToExclude(__DIR__ . '/tests')
    ->ignoreErrorsOnPackage('symfony/validator', [ErrorType::UNUSED_DEPENDENCY])
;
