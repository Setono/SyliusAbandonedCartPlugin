<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Event;

use Doctrine\ORM\QueryBuilder;

final class QueryBuilderForIdleCartsCreated
{
    public function __construct(public readonly QueryBuilder $queryBuilder)
    {
    }
}
