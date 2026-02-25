<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Functional\Controller\Action;

use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\Notification;
use Sylius\Component\Core\Model\Order;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecoverCartActionTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    /** @test */
    public function it_redirects_to_cart_summary_when_cart_exists(): void
    {
        $this->createOrder('recover-token');

        $this->client->request('GET', '/en_US/recover-cart/recover-token');

        self::assertResponseRedirects();
        self::assertStringContainsString('tokenValue=recover-token', (string) $this->client->getResponse()->headers->get('Location'));
    }

    /** @test */
    public function it_updates_last_clicked_at_on_notification(): void
    {
        $order = $this->createOrder('click-token');
        $notification = $this->createNotification($order);

        self::assertNull($notification->getLastClickedAt());

        $this->client->request('GET', '/en_US/recover-cart/click-token');

        self::assertResponseRedirects();

        $em = $this->getEntityManager();
        $em->clear();

        /** @var Notification|null $updatedNotification */
        $updatedNotification = $em->getRepository(Notification::class)->find($notification->getId());
        self::assertNotNull($updatedNotification);
        self::assertNotNull($updatedNotification->getLastClickedAt());
    }

    /** @test */
    public function it_redirects_to_homepage_when_cart_not_found(): void
    {
        $this->client->request('GET', '/en_US/recover-cart/non-existent-token');

        self::assertResponseRedirects();
        self::assertStringContainsString('/', (string) $this->client->getResponse()->headers->get('Location'));
        self::assertStringNotContainsString('recover-cart', (string) $this->client->getResponse()->headers->get('Location'));
    }

    /** @test */
    public function it_forwards_query_parameters_to_cart_url(): void
    {
        $this->createOrder('utm-token');

        $this->client->request('GET', '/en_US/recover-cart/utm-token', [
            'utm_source' => 'email',
            'utm_medium' => 'abandoned_cart',
        ]);

        self::assertResponseRedirects();

        $location = (string) $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('utm_source=email', $location);
        self::assertStringContainsString('utm_medium=abandoned_cart', $location);
        self::assertStringContainsString('tokenValue=utm-token', $location);
    }

    /** @test */
    public function it_redirects_to_cart_summary_even_without_notification(): void
    {
        $this->createOrder('no-notification-token');

        $this->client->request('GET', '/en_US/recover-cart/no-notification-token');

        self::assertResponseRedirects();
        self::assertStringContainsString('tokenValue=no-notification-token', (string) $this->client->getResponse()->headers->get('Location'));
    }

    private function createOrder(string $tokenValue): Order
    {
        $order = new Order();
        $order->setTokenValue($tokenValue);
        $order->setCurrencyCode('USD');
        $order->setLocaleCode('en_US');

        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();

        return $order;
    }

    private function createNotification(Order $order): Notification
    {
        $notification = new Notification();
        $notification->setCart($order);

        $em = $this->getEntityManager();
        $em->persist($notification);
        $em->flush();

        return $notification;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        return $em;
    }
}
