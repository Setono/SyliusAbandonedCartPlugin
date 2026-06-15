<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Setono\SyliusAbandonedCartPlugin\Menu\AdminMenuListener;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListenerTest extends TestCase
{
    #[Test]
    public function it_adds_an_abandoned_cart_item_to_the_marketing_menu(): void
    {
        $factory = new MenuFactory();
        $menu = $factory->createItem('root');
        $menu->addChild('marketing');

        (new AdminMenuListener())->addAdminMenuItems(new MenuBuilderEvent($factory, $menu));

        $marketing = $menu->getChild('marketing');
        self::assertNotNull($marketing);

        $item = $marketing->getChild('abandoned_cart');
        self::assertNotNull($item);
        self::assertSame('setono_sylius_abandoned_cart.ui.abandoned_cart', $item->getLabel());
        self::assertSame('tabler:mail', $item->getLabelAttribute('icon'));
    }

    #[Test]
    public function it_falls_back_to_the_first_child_when_there_is_no_marketing_menu(): void
    {
        $factory = new MenuFactory();
        $menu = $factory->createItem('root');
        $menu->addChild('catalog');

        (new AdminMenuListener())->addAdminMenuItems(new MenuBuilderEvent($factory, $menu));

        $catalog = $menu->getChild('catalog');
        self::assertNotNull($catalog);
        self::assertNotNull($catalog->getChild('abandoned_cart'));
    }
}
