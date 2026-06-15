<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\UrlGenerator;

use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGenerator;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Order;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;

final class CartRecoveryUrlGeneratorTest extends UrlGeneratorAwareTestCase
{
    use ProphecyTrait;

    protected function getRoutes(): iterable
    {
        yield 'sylius_shop_cart_summary' => new Route('/cart');
    }

    #[Test]
    public function it_generates_url(): void
    {
        $channel = new Channel();
        $channel->setHostname('example.com');

        $order = new Order();
        $order->setLocaleCode('en_US');
        $order->setTokenValue('token');
        $order->setChannel($channel);

        $cartRecoveryUrlGenerator = new CartRecoveryUrlGenerator($this->urlGenerator, 'sylius_shop_cart_summary');
        self::assertSame('https://example.com/cart?tokenValue=token&utm_source=sylius&utm_medium=email&utm_campaign=Abandoned%20Cart&_locale=en_US', $cartRecoveryUrlGenerator->generate($order));
    }

    #[Test]
    public function it_allows_to_overwrite_parameters(): void
    {
        $channel = new Channel();
        $channel->setHostname('example.com');

        $order = new Order();
        $order->setLocaleCode('en_US');
        $order->setTokenValue('token');
        $order->setChannel($channel);

        $cartRecoveryUrlGenerator = new CartRecoveryUrlGenerator($this->urlGenerator, 'sylius_shop_cart_summary');
        self::assertSame('https://example.com/cart?tokenValue=token&utm_source=sylius&utm_medium=email&utm_campaign=Abandoned%20Cart%20%232&_locale=en_US&utm_content=Number%20two', $cartRecoveryUrlGenerator->generate($order, [
            'utm_campaign' => 'Abandoned Cart #2',
            'utm_content' => 'Number two',
        ]));
    }

    #[Test]
    public function it_falls_back_to_a_locale_less_url_when_the_store_has_no_locale_based_channels(): void
    {
        $channel = new Channel();
        $channel->setHostname('example.com');

        $order = new Order();
        $order->setLocaleCode('en_US');
        $order->setTokenValue('token');
        $order->setChannel($channel);

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate(
            'sylius_shop_cart_summary',
            Argument::that(static fn (array $parameters): bool => array_key_exists('_locale', $parameters)),
            UrlGeneratorInterface::ABSOLUTE_PATH,
        )->willThrow(new SessionNotFoundException());
        $urlGenerator->generate(
            'sylius_shop_cart_summary',
            Argument::that(static fn (array $parameters): bool => !array_key_exists('_locale', $parameters)),
            UrlGeneratorInterface::ABSOLUTE_PATH,
        )->willReturn('/cart?tokenValue=token');
        $urlGenerator->getContext()->willReturn(new RequestContext('', 'GET', 'example.com', 'https'));

        $generator = new CartRecoveryUrlGenerator($urlGenerator->reveal(), 'sylius_shop_cart_summary');

        self::assertSame('https://example.com/cart?tokenValue=token', $generator->generate($order));
    }
}
