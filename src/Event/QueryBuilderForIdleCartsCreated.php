<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Event;

use Doctrine\ORM\QueryBuilder;

final readonly class QueryBuilderForIdleCartsCreated
{
    public function __construct(public QueryBuilder $queryBuilder)
    {
    }
}
