<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

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
        array $parameters = []
    ): string {
        $channel = $order->getChannel();
        Assert::notNull($channel);

        $parameters = array_merge([
            'tokenValue' => $order->getTokenValue(),
            'utm_source' => 'sylius',
            'utm_medium' => 'email',
            'utm_campaign' => 'Abandoned Cart',
            '_locale' => $order->getLocaleCode(),
        ], $parameters);

        return sprintf(
            '%s://%s%s',
            $this->urlGenerator->getContext()->getScheme(),
            (string) $channel->getHostname(),
            $this->urlGenerator->generate($this->route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
        );
    }
}
