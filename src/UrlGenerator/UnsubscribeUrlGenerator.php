<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UnsubscribeUrlGenerator implements UnsubscribeUrlGeneratorInterface
{
    private UrlGeneratorInterface $urlGenerator;

    private EmailHasherInterface $emailHasher;

    private string $route;

    public function __construct(UrlGeneratorInterface $urlGenerator, EmailHasherInterface $emailHasher, string $route)
    {
        $this->urlGenerator = $urlGenerator;
        $this->emailHasher = $emailHasher;
        $this->route = $route;
    }

    public function generate(
        string $email,
        string $locale,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ): string {
        $parameters = array_merge([
            'email' => $email,
            'hash' => $this->emailHasher->hash($email),
            'utm_source' => 'sylius',
            'utm_medium' => 'email',
            'utm_campaign' => 'Abandoned Cart Unsubscribe',
            '_locale' => $locale,
        ], $parameters);

        return $this->urlGenerator->generate($this->route, $parameters, $referenceType);
    }
}
