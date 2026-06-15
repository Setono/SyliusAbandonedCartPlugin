<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Context;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Context\TokenValueBasedCartContext;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class TokenValueBasedCartContextTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
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

    #[Test]
    public function it_throws_when_there_is_no_main_request(): void
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $context = new TokenValueBasedCartContext(new RequestStack(), $orderRepository->reveal());

        $this->expectException(CartNotFoundException::class);

        $context->getCart();
    }

    #[Test]
    public function it_throws_when_the_token_value_is_not_a_string(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $context = new TokenValueBasedCartContext($requestStack, $orderRepository->reveal());

        $this->expectException(CartNotFoundException::class);

        $context->getCart();
    }

    #[Test]
    public function it_throws_when_no_cart_matches_the_token(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(['tokenValue' => 'missing']));

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findCartByTokenValue('missing')->willReturn(null);
        $context = new TokenValueBasedCartContext($requestStack, $orderRepository->reveal());

        $this->expectException(CartNotFoundException::class);

        $context->getCart();
    }
}
