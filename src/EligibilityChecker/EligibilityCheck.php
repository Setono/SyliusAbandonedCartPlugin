<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

use Webmozart\Assert\Assert;

final class EligibilityCheck
{
    public bool $eligible;

    /** @var list<string> */
    public array $reasons;

    /**
     * @param string|list<string> $reasons
     */
    public function __construct(bool $eligible, $reasons = [])
    {
        if (is_string($reasons)) {
            $reasons = [$reasons];
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        Assert::isArray($reasons);

        $this->eligible = $eligible;
        $this->reasons = $reasons;
    }
}
