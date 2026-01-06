<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\UrlGenerator;

use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGenerator;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Order;
use Symfony\Component\Routing\Route;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\UrlGenerator\CartRecoveryUrlGenerator
 */
final class CartRecoveryUrlGeneratorTest extends UrlGeneratorAwareTestCase
{
    use ProphecyTrait;

    protected function getRoutes(): iterable
    {
        yield 'sylius_shop_cart_summary' => new Route('/cart');
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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
}
