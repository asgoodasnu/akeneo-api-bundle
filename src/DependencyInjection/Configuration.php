<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treebuilder = new TreeBuilder('asgoodasnew_akeneo_api');

        $rootNode = $treebuilder->getRootNode();

        $rootNode->children()
            ->scalarNode('url')->isRequired()->end()
            ->scalarNode('user')->isRequired()->end()
            ->scalarNode('password')->isRequired()->end()
            ->scalarNode('token')->isRequired()->end()
            ->booleanNode('cached')->defaultFalse()->end()
        ->end();

        return $treebuilder;
    }
}
