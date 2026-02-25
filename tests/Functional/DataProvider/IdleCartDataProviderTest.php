<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Functional\DataProvider;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusAbandonedCartPlugin\DataProvider\IdleCartDataProviderInterface;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IdleCartDataProviderTest extends KernelTestCase
{
    /** @test */
    public function it_returns_idle_carts_without_notifications(): void
    {
        $customer = $this->createCustomer('idle@example.com');
        $this->createOrder('idle-token', $customer);

        $carts = $this->getCarts();

        self::assertCount(1, $carts);
        self::assertSame('idle-token', $carts[0]->getTokenValue());
    }

    /** @test */
    public function it_excludes_carts_that_already_have_a_notification(): void
    {
        $customer = $this->createCustomer('notified@example.com');
        $order = $this->createOrder('notified-token', $customer);

        $notification = new Notification();
        $notification->setCart($order);
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();

        self::assertCount(0, $this->getCarts());
    }

    /** @test */
    public function it_excludes_carts_without_a_customer(): void
    {
        $this->createOrder('no-customer-token', null);

        self::assertCount(0, $this->getCarts());
    }

    /** @test */
    public function it_excludes_carts_not_in_cart_state(): void
    {
        $customer = $this->createCustomer('completed@example.com');
        $this->createOrder('completed-token', $customer, OrderInterface::STATE_NEW);

        self::assertCount(0, $this->getCarts());
    }

    /** @test */
    public function it_excludes_carts_outside_the_lookback_window(): void
    {
        $customer = $this->createCustomer('old@example.com');
        // lookback_window defaults to 15 min, idle_threshold is 0 in test config
        // So updatedAt must be >= now - 15 minutes. Set it to 1 hour ago to be outside the window.
        $this->createOrder('old-token', $customer, OrderInterface::STATE_CART, new DateTimeImmutable('-1 hour'));

        self::assertCount(0, $this->getCarts());
    }

    /** @test */
    public function it_returns_multiple_idle_carts(): void
    {
        $customer1 = $this->createCustomer('multi1@example.com');
        $customer2 = $this->createCustomer('multi2@example.com');
        $this->createOrder('multi-token-1', $customer1);
        $this->createOrder('multi-token-2', $customer2);

        self::assertCount(2, $this->getCarts());
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
        ?DateTimeImmutable $updatedAt = null,
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

        if (null !== $updatedAt) {
            // Update directly via DBAL to bypass Doctrine's automatic timestamping
            $em->getConnection()->executeStatement(
                'UPDATE sylius_order SET updated_at = :updatedAt WHERE token_value = :tokenValue',
                ['updatedAt' => $updatedAt->format('Y-m-d H:i:s'), 'tokenValue' => $tokenValue],
            );
            $em->clear();
        }

        return $order;
    }

    /**
     * @return list<OrderInterface>
     */
    private function getCarts(): array
    {
        $carts = [];
        foreach ($this->getProvider()->getCarts() as $cart) {
            $carts[] = $cart;
        }

        return $carts;
    }

    private function getProvider(): IdleCartDataProviderInterface
    {
        /** @var IdleCartDataProviderInterface $provider */
        $provider = self::getContainer()->get(IdleCartDataProviderInterface::class);

        return $provider;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        return $em;
    }
}
