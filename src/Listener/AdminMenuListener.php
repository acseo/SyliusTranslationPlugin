<?php

namespace Yaroslavche\SyliusTranslationPlugin\Listener;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->getChild('configuration')
            ->addChild('translation_dashboard', ['route' => 'yaroslavche_sylius_translation_plugin_dashboard'])
            ->setLabel('Translation')
            ->setLabelAttribute('icon', 'translate')
        ;
    }
}
