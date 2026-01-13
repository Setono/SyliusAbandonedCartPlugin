<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Controller\Action;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusAbandonedCartPlugin\Controller\Action\RecoverCartAction;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Setono\SyliusAbandonedCartPlugin\Repository\NotificationRepositoryInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers \Setono\SyliusAbandonedCartPlugin\Controller\Action\RecoverCartAction
 */
final class RecoverCartActionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_redirects_to_cart_and_updates_last_clicked_at(): void
    {
        $order = new Order();
        $notification = new Notification();
        $notification->setCart($order);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findCartByTokenValue('test-token')->willReturn($order);

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->findOneByOrder($order)->willReturn($notification);

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate('sylius_shop_cart_summary', [
            'tokenValue' => 'test-token',
            'utm_source' => 'sylius',
        ])->willReturn('/cart?tokenValue=test-token&utm_source=sylius');

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Notification::class)->willReturn($entityManager->reveal());

        $requestStack = $this->prophesize(RequestStack::class);

        $action = new RecoverCartAction(
            $orderRepository->reveal(),
            $notificationRepository->reveal(),
            $urlGenerator->reveal(),
            $requestStack->reveal(),
            $managerRegistry->reveal(),
        );

        $request = new Request(['utm_source' => 'sylius']);
        $response = $action($request, 'test-token');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertNotNull($notification->getLastClickedAt());
    }

    /**
     * @test
     */
    public function it_redirects_to_homepage_when_cart_not_found(): void
    {
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findCartByTokenValue('invalid-token')->willReturn(null);

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate('sylius_shop_homepage')->willReturn('/');

        $flashBag = $this->prophesize(FlashBagInterface::class);
        $flashBag->add('error', 'setono_sylius_abandoned_cart.ui.cart_recovery_link_expired')->shouldBeCalled();

        $session = $this->prophesize(Session::class);
        $session->getFlashBag()->willReturn($flashBag->reveal());

        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getSession()->willReturn($session->reveal());

        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $action = new RecoverCartAction(
            $orderRepository->reveal(),
            $notificationRepository->reveal(),
            $urlGenerator->reveal(),
            $requestStack->reveal(),
            $managerRegistry->reveal(),
        );

        $request = new Request();
        $response = $action($request, 'invalid-token');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function it_forwards_all_query_parameters_to_cart_url(): void
    {
        $order = new Order();
        $notification = new Notification();
        $notification->setCart($order);

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findCartByTokenValue('test-token')->willReturn($order);

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->findOneByOrder($order)->willReturn($notification);

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate('sylius_shop_cart_summary', [
            'utm_source' => 'sylius',
            'utm_medium' => 'email',
            'custom_param' => 'value',
            'tokenValue' => 'test-token',
        ])->willReturn('/cart?tokenValue=test-token&utm_source=sylius&utm_medium=email&custom_param=value');

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $entityManager->flush()->shouldBeCalled();

        $managerRegistry = $this->prophesize(ManagerRegistry::class);
        $managerRegistry->getManagerForClass(Notification::class)->willReturn($entityManager->reveal());

        $requestStack = $this->prophesize(RequestStack::class);

        $action = new RecoverCartAction(
            $orderRepository->reveal(),
            $notificationRepository->reveal(),
            $urlGenerator->reveal(),
            $requestStack->reveal(),
            $managerRegistry->reveal(),
        );

        $request = new Request([
            'utm_source' => 'sylius',
            'utm_medium' => 'email',
            'custom_param' => 'value',
        ]);
        $response = $action($request, 'test-token');

        self::assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @test
     */
    public function it_handles_cart_without_notification(): void
    {
        $order = new Order();

        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $orderRepository->findCartByTokenValue('test-token')->willReturn($order);

        $notificationRepository = $this->prophesize(NotificationRepositoryInterface::class);
        $notificationRepository->findOneByOrder($order)->willReturn(null);

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate('sylius_shop_cart_summary', [
            'tokenValue' => 'test-token',
        ])->willReturn('/cart?tokenValue=test-token');

        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $requestStack = $this->prophesize(RequestStack::class);

        $action = new RecoverCartAction(
            $orderRepository->reveal(),
            $notificationRepository->reveal(),
            $urlGenerator->reveal(),
            $requestStack->reveal(),
            $managerRegistry->reveal(),
        );

        $request = new Request();
        $response = $action($request, 'test-token');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/cart?tokenValue=test-token', $response->getTargetUrl());
    }
}
