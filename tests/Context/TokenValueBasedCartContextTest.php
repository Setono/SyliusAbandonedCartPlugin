<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Context;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Context\TokenValueBasedCartContext;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\Context\TokenValueBasedCartContext
 */
final class TokenValueBasedCartContextTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_returns_cart(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request([
            'tokenValue' => 'token',
        ]));

        $order = new Order();

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findCartByTokenValue('token')->willReturn($order);
        $context = new TokenValueBasedCartContext($requestStack, $orderRepository->reveal());

        self::assertSame($order, $context->getCart());
    }
}
