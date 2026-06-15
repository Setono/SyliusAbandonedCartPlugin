<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Event;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Event\QueryBuilderForIdleCartsCreated;

final class QueryBuilderForIdleCartsCreatedTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function it_exposes_the_query_builder(): void
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class)->reveal();

        self::assertSame($queryBuilder, (new QueryBuilderForIdleCartsCreated($queryBuilder))->queryBuilder);
    }
}
