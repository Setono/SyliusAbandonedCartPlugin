<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CartRecoveryUrlGenerator implements CartRecoveryUrlGeneratorInterface
{
    private UrlGeneratorInterface $urlGenerator;

    private string $route;

    public function __construct(UrlGeneratorInterface $urlGenerator, string $route)
    {
        $this->urlGenerator = $urlGenerator;
        $this->route = $route;
    }

    public function generate(
        OrderInterface $order,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ): string {
        $parameters = array_merge([
            'tokenValue' => $order->getTokenValue(),
            'utm_source' => 'sylius',
            'utm_medium' => 'email',
            'utm_campaign' => 'Abandoned Cart',
            '_locale' => $order->getLocaleCode(),
        ], $parameters);

        return $this->urlGenerator->generate($this->route, $parameters, $referenceType);
    }
}
