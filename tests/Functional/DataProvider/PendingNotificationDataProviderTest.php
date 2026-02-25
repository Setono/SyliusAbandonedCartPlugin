<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Functional\DataProvider;

use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusAbandonedCartPlugin\DataProvider\PendingNotificationDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Setono\SyliusAbandonedCartPlugin\Model\NotificationInterface;
use Setono\SyliusAbandonedCartPlugin\Workflow\NotificationWorkflow;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PendingNotificationDataProviderTest extends KernelTestCase
{
    /** @test */
    public function it_returns_pending_notifications_with_idle_carts(): void
    {
        $customer = $this->createCustomer('pending@example.com');
        $order = $this->createOrder('pending-token', $customer);
        $this->createNotification($order);

        $notifications = $this->getNotifications();

        self::assertCount(1, $notifications);

        $cart = $notifications[0]->getCart();
        self::assertNotNull($cart);
        self::assertSame('pending-token', $cart->getTokenValue());
    }

    /** @test */
    public function it_excludes_notifications_not_in_pending_state(): void
    {
        $customer = $this->createCustomer('processing@example.com');
        $order = $this->createOrder('processing-token', $customer);
        $this->createNotification($order, NotificationWorkflow::STATE_PROCESSING);

        self::assertCount(0, $this->getNotifications());
    }

    /** @test */
    public function it_excludes_notifications_whose_cart_is_no_longer_in_cart_state(): void
    {
        $customer = $this->createCustomer('completed@example.com');
        $order = $this->createOrder('completed-token', $customer, OrderInterface::STATE_NEW);
        $this->createNotification($order);

        self::assertCount(0, $this->getNotifications());
    }

    /** @test */
    public function it_excludes_notifications_whose_cart_has_no_customer(): void
    {
        $order = $this->createOrder('no-customer-token', null);
        $this->createNotification($order);

        self::assertCount(0, $this->getNotifications());
    }

    /** @test */
    public function it_returns_multiple_pending_notifications(): void
    {
        $customer1 = $this->createCustomer('multi1@example.com');
        $customer2 = $this->createCustomer('multi2@example.com');
        $order1 = $this->createOrder('multi-token-1', $customer1);
        $order2 = $this->createOrder('multi-token-2', $customer2);
        $this->createNotification($order1);
        $this->createNotification($order2);

        self::assertCount(2, $this->getNotifications());
    }

    private function createCustomer(string $email): Customer
    {
        $customer = new Customer();
        $customer->setEmail($email);

        $this->getEntityManager()->persist($customer);
        $this->getEntityManager()->flush();

        return $customer;
    }

    private function createOrder(
        string $tokenValue,
        ?Customer $customer,
        string $state = OrderInterface::STATE_CART,
    ): Order {
        $order = new Order();
        $order->setTokenValue($tokenValue);
        $order->setCurrencyCode('USD');
        $order->setLocaleCode('en_US');
        $order->setState($state);

        if (null !== $customer) {
            $order->setCustomer($customer);
        }

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }

    private function createNotification(Order $order, string $state = NotificationWorkflow::STATE_PENDING): Notification
    {
        $notification = new Notification();
        $notification->setCart($order);
        $notification->setState($state);

        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();

        return $notification;
    }

    /**
     * @return list<NotificationInterface>
     */
    private function getNotifications(): array
    {
        $notifications = [];
        foreach ($this->getProvider()->getNotifications() as $notification) {
            $notifications[] = $notification;
        }

        return $notifications;
    }

    private function getProvider(): PendingNotificationDataProviderInterface
    {
        /** @var PendingNotificationDataProviderInterface $provider */
        $provider = self::getContainer()->get(PendingNotificationDataProviderInterface::class);

        return $provider;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        return $em;
    }
}
