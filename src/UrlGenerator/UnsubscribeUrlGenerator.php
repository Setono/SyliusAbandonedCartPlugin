<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\UrlGenerator;

use Setono\SyliusAbandonedCartPlugin\Hasher\EmailHasherInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
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
        ChannelInterface $channel,
        string $email,
        string $locale,
        array $parameters = [],
    ): string {
        $parameters = array_merge([
            'email' => $email,
            'hash' => $this->emailHasher->hash($email),
            'utm_source' => 'sylius',
            'utm_medium' => 'email',
            'utm_campaign' => 'Abandoned Cart Unsubscribe',
            '_locale' => $locale,
        ], $parameters);

        try {
            $path = $this->urlGenerator->generate($this->route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (SessionNotFoundException $e) {
            // it's a long story, but this exception is thrown if the store doesn't use locale based channels
            unset($parameters['_locale']);
            $path = $this->urlGenerator->generate($this->route, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return sprintf(
            '%s://%s%s',
            $this->urlGenerator->getContext()->getScheme(),
            (string) $channel->getHostname(),
            $path,
        );
    }
}
