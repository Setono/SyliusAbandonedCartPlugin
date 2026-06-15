<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\UrlGenerator;

use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasher;
use Setono\SyliusAbandonedCartPlugin\UrlGenerator\UnsubscribeUrlGenerator;
use Sylius\Component\Core\Model\Channel;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;

final class UnsubscribeUrlGeneratorTest extends UrlGeneratorAwareTestCase
{
    use ProphecyTrait;

    protected function getRoutes(): iterable
    {
        yield 'setono_sylius_abandoned_cart_shop_unsubscribe_customer' => new Route('/abandoned-cart/unsubscribe');
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_falls_back_to_a_locale_less_url_when_the_store_has_no_locale_based_channels(): void
    {
        $route = 'setono_sylius_abandoned_cart_shop_unsubscribe_customer';

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate(
            $route,
            Argument::that(static fn (array $parameters): bool => array_key_exists('_locale', $parameters)),
            UrlGeneratorInterface::ABSOLUTE_PATH,
        )->willThrow(new SessionNotFoundException());
        $urlGenerator->generate(
            $route,
            Argument::that(static fn (array $parameters): bool => !array_key_exists('_locale', $parameters)),
            UrlGeneratorInterface::ABSOLUTE_PATH,
        )->willReturn('/abandoned-cart/unsubscribe?email=johndoe@example.com');
        $urlGenerator->getContext()->willReturn(new RequestContext('', 'GET', 'example.com', 'https'));

        $generator = new UnsubscribeUrlGenerator($urlGenerator->reveal(), new EmailHasher('salt'), $route);

        $channel = new Channel();
        $channel->setHostname('example.com');

        self::assertSame(
            'https://example.com/abandoned-cart/unsubscribe?email=johndoe@example.com',
            $generator->generate($channel, 'johndoe@example.com', 'en_US'),
        );
    }
}
