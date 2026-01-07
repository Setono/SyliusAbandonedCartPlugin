<?php

declare(strict_types=1);

use Setono\SyliusAbandonedCartPlugin\Tests\Application\Kernel;

require __DIR__ . '/../Application/config/bootstrap.php';

$kernel = new Kernel('test', true);
$kernel->boot();

/** @phpstan-ignore method.notFound,method.nonObject */
return $kernel->getContainer()->get('doctrine')->getManager();
