<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Context;

use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class TokenValueBasedCartContext implements CartContextInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function getCart(): OrderInterface
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new CartNotFoundException('There is no main request on the request stack.');
        }

        $tokenValue = $request->query->get('tokenValue');
        if (!is_string($tokenValue)) {
            throw new CartNotFoundException('The token value is not a string.');
        }

        $cart = $this->orderRepository->findCartByTokenValue($tokenValue);
        if (null === $cart) {
            throw new CartNotFoundException('No cart exists with the given token.');
        }

        return $cart;
    }
}
