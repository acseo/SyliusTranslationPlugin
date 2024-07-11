<?php

declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('yaroslavche_sylius_translation_plugin');
        $rootNode =  $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
