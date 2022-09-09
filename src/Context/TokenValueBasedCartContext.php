<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Context;

use Setono\MainRequestTrait\MainRequestTrait;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Context\CartNotFoundException;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class TokenValueBasedCartContext implements CartContextInterface
{
    use MainRequestTrait;

    private RequestStack $requestStack;

    private OrderRepositoryInterface $orderRepository;

    public function __construct(RequestStack $requestStack, OrderRepositoryInterface $orderRepository)
    {
        $this->requestStack = $requestStack;
        $this->orderRepository = $orderRepository;
    }

    public function getCart(): OrderInterface
    {
        $request = $this->getMainRequest();
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

    private function getMainRequest(): Request
    {
        $request = $this->getMainRequestFromRequestStack($this->requestStack);
        if (null === $request) {
            throw new CartNotFoundException('There is no main request on the request stack.');
        }

        return $request;
    }
}
