<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Command;

use Setono\SyliusAbandonedCartPlugin\Pruner\PrunerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PruneCommand extends Command
{
    protected static $defaultName = 'setono:sylius-abandoned-cart:prune';

    /** @var string|null */
    protected static $defaultDescription = 'Prune older notifications';

    private PrunerInterface $pruner;

    public function __construct(PrunerInterface $pruner)
    {
        parent::__construct();

        $this->pruner = $pruner;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pruner->prune();

        return 0;
    }
}
