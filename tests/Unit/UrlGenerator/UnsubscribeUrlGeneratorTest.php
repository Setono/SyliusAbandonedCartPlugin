<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\UrlGenerator;

use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasher;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGenerator;
use Sylius\Component\Core\Model\Channel;
use Symfony\Component\Routing\Route;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGenerator
 */
final class UnsubscribeUrlGeneratorTest extends UrlGeneratorAwareTestCase
{
    use ProphecyTrait;

    protected function getRoutes(): iterable
    {
        yield 'setono_sylius_abandoned_cart_shop_unsubscribe_customer' => new Route('/abandoned-cart/unsubscribe');
    }

    /**
     * @test
     */
    public function it_generates_url(): void
    {
        $urlGenerator = new UnsubscribeUrlGenerator(
            $this->urlGenerator,
            new EmailHasher('salt'),
            'setono_sylius_abandoned_cart_shop_unsubscribe_customer',
        );

        $channel = new Channel();
        $channel->setHostname('example.com');

        self::assertSame(
            'https://example.com/abandoned-cart/unsubscribe?email=johndoe@example.com&hash=2ac21379842b5445001475a596caab5843ecbc6be46c27f882cb6c0bd75fb9f9&utm_source=sylius&utm_medium=email&utm_campaign=Abandoned%20Cart%20Unsubscribe&_locale=en_US',
            $urlGenerator->generate($channel, 'johndoe@example.com', 'en_US'),
        );
    }

    /**
     * @test
     */
    public function it_allows_to_overwrite_parameters(): void
    {
        $urlGenerator = new UnsubscribeUrlGenerator(
            $this->urlGenerator,
            new EmailHasher('salt'),
            'setono_sylius_abandoned_cart_shop_unsubscribe_customer',
        );

        $channel = new Channel();
        $channel->setHostname('example.com');

        self::assertSame(
            'https://example.com/abandoned-cart/unsubscribe?email=johndoe@example.com&hash=2ac21379842b5445001475a596caab5843ecbc6be46c27f882cb6c0bd75fb9f9&utm_source=sylius&utm_medium=email&utm_campaign=Abandoned%20Cart%20Unsubscribe%20%232&_locale=en_US&utm_content=Number%20two',
            $urlGenerator->generate($channel, 'johndoe@example.com', 'en_US', [
                'utm_campaign' => 'Abandoned Cart Unsubscribe #2',
                'utm_content' => 'Number two',
            ]),
        );
    }
}
