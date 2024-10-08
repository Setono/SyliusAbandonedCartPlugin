<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\EligibilityChecker;

final class EligibilityCheck
{
    /** @var list<string> */
    public array $reasons;

    /**
     * @param string|list<string> $reasons
     */
    public function __construct(public bool $eligible, array|string $reasons = [])
    {
        if (is_string($reasons)) {
            $reasons = [$reasons];
        }

        $this->reasons = $reasons;
    }
}
