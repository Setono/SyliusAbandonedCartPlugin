<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Controller\Action;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Controller\Action\UnsubscribeCustomerAction;
use Setono\SyliusAbandonedCartPlugin\Factory\UnsubscribedCustomerFactoryInterface;
use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomerInterface;
use Setono\SyliusAbandonedCartPlugin\Repository\UnsubscribedCustomerRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

final class UnsubscribeCustomerActionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_unsubscribes_customer_successfully(): void
    {
        $emailHasher = $this->prophesize(EmailHasherInterface::class);
        $emailHasher->hash('customer@example.com')->willReturn('validhash');

        $unsubscribedCustomer = $this->prophesize(UnsubscribedCustomerInterface::class);

        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $repository->isUnsubscribed('customer@example.com')->willReturn(false);
        $repository->add($unsubscribedCustomer->reveal())->shouldBeCalled();

        $factory = $this->prophesize(UnsubscribedCustomerFactoryInterface::class);
        $factory->createWithEmail('customer@example.com')->willReturn($unsubscribedCustomer->reveal());

        $twig = $this->prophesize(Environment::class);
        $twig->render('@SetonoSyliusAbandonedCartPlugin/shop/unsubscribe.html.twig', ['error' => null])->willReturn('success');

        $action = new UnsubscribeCustomerAction(
            $emailHasher->reveal(),
            $repository->reveal(),
            $factory->reveal(),
            $twig->reveal(),
        );

        $request = new Request(['email' => 'customer@example.com', 'hash' => 'validhash']);

        $response = $action($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('success', $response->getContent());
    }

    /**
     * @test
     */
    public function it_renders_error_when_email_is_missing(): void
    {
        $emailHasher = $this->prophesize(EmailHasherInterface::class);
        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $factory = $this->prophesize(UnsubscribedCustomerFactoryInterface::class);

        $twig = $this->prophesize(Environment::class);
        $twig->render(
            '@SetonoSyliusAbandonedCartPlugin/shop/unsubscribe.html.twig',
            ['error' => 'setono_sylius_abandoned_cart.ui.no_email_provided'],
        )->willReturn('error page');

        $action = new UnsubscribeCustomerAction(
            $emailHasher->reveal(),
            $repository->reveal(),
            $factory->reveal(),
            $twig->reveal(),
        );

        $request = new Request();

        $response = $action($request);

        self::assertSame(200, $response->getStatusCode());
        $repository->add(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_renders_error_when_hash_is_missing(): void
    {
        $emailHasher = $this->prophesize(EmailHasherInterface::class);
        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $factory = $this->prophesize(UnsubscribedCustomerFactoryInterface::class);

        $twig = $this->prophesize(Environment::class);
        $twig->render(
            '@SetonoSyliusAbandonedCartPlugin/shop/unsubscribe.html.twig',
            ['error' => 'setono_sylius_abandoned_cart.ui.no_hash_provided'],
        )->willReturn('error page');

        $action = new UnsubscribeCustomerAction(
            $emailHasher->reveal(),
            $repository->reveal(),
            $factory->reveal(),
            $twig->reveal(),
        );

        $request = new Request(['email' => 'customer@example.com']);

        $response = $action($request);

        self::assertSame(200, $response->getStatusCode());
        $repository->add(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_renders_error_when_hash_is_invalid(): void
    {
        $emailHasher = $this->prophesize(EmailHasherInterface::class);
        $emailHasher->hash('customer@example.com')->willReturn('correcthash');

        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $factory = $this->prophesize(UnsubscribedCustomerFactoryInterface::class);

        $twig = $this->prophesize(Environment::class);
        $twig->render(
            '@SetonoSyliusAbandonedCartPlugin/shop/unsubscribe.html.twig',
            ['error' => 'setono_sylius_abandoned_cart.ui.invalid_hash'],
        )->willReturn('error page');

        $action = new UnsubscribeCustomerAction(
            $emailHasher->reveal(),
            $repository->reveal(),
            $factory->reveal(),
            $twig->reveal(),
        );

        $request = new Request(['email' => 'customer@example.com', 'hash' => 'wronghash']);

        $response = $action($request);

        self::assertSame(200, $response->getStatusCode());
        $repository->add(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_renders_error_when_already_unsubscribed(): void
    {
        $emailHasher = $this->prophesize(EmailHasherInterface::class);
        $emailHasher->hash('customer@example.com')->willReturn('validhash');

        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $repository->isUnsubscribed('customer@example.com')->willReturn(true);

        $factory = $this->prophesize(UnsubscribedCustomerFactoryInterface::class);

        $twig = $this->prophesize(Environment::class);
        $twig->render(
            '@SetonoSyliusAbandonedCartPlugin/shop/unsubscribe.html.twig',
            ['error' => 'setono_sylius_abandoned_cart.ui.email_already_unsubscribed'],
        )->willReturn('error page');

        $action = new UnsubscribeCustomerAction(
            $emailHasher->reveal(),
            $repository->reveal(),
            $factory->reveal(),
            $twig->reveal(),
        );

        $request = new Request(['email' => 'customer@example.com', 'hash' => 'validhash']);

        $response = $action($request);

        self::assertSame(200, $response->getStatusCode());
        $repository->add(Argument::any())->shouldNotHaveBeenCalled();
    }

    /**
     * @test
     */
    public function it_normalizes_email_to_lowercase(): void
    {
        $emailHasher = $this->prophesize(EmailHasherInterface::class);
        $emailHasher->hash('customer@example.com')->willReturn('validhash');

        $unsubscribedCustomer = $this->prophesize(UnsubscribedCustomerInterface::class);

        $repository = $this->prophesize(UnsubscribedCustomerRepositoryInterface::class);
        $repository->isUnsubscribed('customer@example.com')->willReturn(false);
        $repository->add($unsubscribedCustomer->reveal())->shouldBeCalled();

        $factory = $this->prophesize(UnsubscribedCustomerFactoryInterface::class);
        $factory->createWithEmail('customer@example.com')->willReturn($unsubscribedCustomer->reveal());

        $twig = $this->prophesize(Environment::class);
        $twig->render(Argument::cetera())->willReturn('success');

        $action = new UnsubscribeCustomerAction(
            $emailHasher->reveal(),
            $repository->reveal(),
            $factory->reveal(),
            $twig->reveal(),
        );

        $request = new Request(['email' => 'Customer@Example.COM', 'hash' => 'validhash']);

        $action($request);

        // The hash is computed with the lowercase email
        $emailHasher->hash('customer@example.com')->shouldHaveBeenCalled();
    }
}
