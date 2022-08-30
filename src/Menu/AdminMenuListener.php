<?php

declare(strict_types=1);

namespace Setono\SyliusAbandonedCartPlugin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $this->addChild($menu);
    }

    private function addChild(ItemInterface $menu): void
    {
        $submenu = $menu->getChild('marketing');
        $item = $submenu instanceof ItemInterface ? $submenu : $menu->getFirstChild();
        $item
            ->addChild('abandoned_cart', [
                'route' => 'setono_sylius_abandoned_cart_admin_notification_index',
            ])
            ->setLabel('setono_sylius_abandoned_cart.ui.abandoned_cart')
            ->setLabelAttribute('icon', 'envelope outline')
        ;
    }
}
