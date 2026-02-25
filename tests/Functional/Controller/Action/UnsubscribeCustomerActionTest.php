<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Functional\Controller\Action;

use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusAbandonedCartPlugin\Model\UnsubscribedCustomer;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UnsubscribeCustomerActionTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    /** @test */
    public function it_unsubscribes_a_customer(): void
    {
        self::assertFalse($this->isUnsubscribed('john@example.com'));

        $this->client->request('GET', '/en_US/abandoned-cart/unsubscribe', [
            'email' => 'john@example.com',
            'hash' => $this->computeHash('john@example.com'),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.ui.icon.header', 'You have been unsubscribed');

        self::assertTrue($this->isUnsubscribed('john@example.com'));
    }

    /** @test */
    public function it_normalizes_email_to_lowercase(): void
    {
        $this->client->request('GET', '/en_US/abandoned-cart/unsubscribe', [
            'email' => 'John@Example.COM',
            'hash' => $this->computeHash('john@example.com'),
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.ui.icon.header', 'You have been unsubscribed');

        self::assertTrue($this->isUnsubscribed('john@example.com'));
    }

    /** @test */
    public function it_shows_error_when_email_is_missing(): void
    {
        $this->client->request('GET', '/en_US/abandoned-cart/unsubscribe');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.sub.header', 'You need to provide an email');
    }

    /** @test */
    public function it_shows_error_when_hash_is_missing(): void
    {
        $this->client->request('GET', '/en_US/abandoned-cart/unsubscribe', [
            'email' => 'john@example.com',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.sub.header', 'You need to provide a hash to unsubscribe');
    }

    /** @test */
    public function it_shows_error_when_hash_is_invalid(): void
    {
        $this->client->request('GET', '/en_US/abandoned-cart/unsubscribe', [
            'email' => 'john@example.com',
            'hash' => 'invalid_hash',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.sub.header', 'The hash is invalid');
    }

    /** @test */
    public function it_shows_error_when_email_is_already_unsubscribed(): void
    {
        $hash = $this->computeHash('john@example.com');

        $this->client->request('GET', '/en_US/abandoned-cart/unsubscribe', [
            'email' => 'john@example.com',
            'hash' => $hash,
        ]);

        self::assertResponseIsSuccessful();

        // Try to unsubscribe the same email again
        $this->client->request('GET', '/en_US/abandoned-cart/unsubscribe', [
            'email' => 'john@example.com',
            'hash' => $hash,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.sub.header', 'The email is already unsubscribed');
    }

    private function computeHash(string $email): string
    {
        return hash('sha256', strtolower($email) . 's3cr3t');
    }

    private function isUnsubscribed(string $email): bool
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        return null !== $em->getRepository(UnsubscribedCustomer::class)->findOneBy(['email' => $email]);
    }
}
