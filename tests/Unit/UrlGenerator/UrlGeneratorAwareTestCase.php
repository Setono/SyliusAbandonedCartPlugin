<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\UrlGenerator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

abstract class UrlGeneratorAwareTestCase extends TestCase
{
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $routeCollection = new RouteCollection();
        foreach ($this->getRoutes() as $name => $route) {
            $routeCollection->add($name, $route);
        }

        $this->urlGenerator = new UrlGenerator($routeCollection, new RequestContext('', 'GET', 'example.com', 'https'));
    }

    /**
     * @return iterable<string, Route>
     */
    abstract protected function getRoutes(): iterable;
}
