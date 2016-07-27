<?php

namespace Garant\FilePreviewGeneratorBundle\DependencyInjection;

use Garant\FilePreviewGeneratorBundle\Client\RemoteClient;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('garant_file_preview_generator');

        $serverNode = (new TreeBuilder())->root('servers');
        $serverNode->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->scalarNode('protocol')->defaultValue('http')->end()
                ->scalarNode('host')->isRequired()->end()
                ->scalarNode('port')->isRequired()->end()
            ->end()
            ->end()
        ;

        $rootNode
            ->children()
                ->enumNode('server_select_algorithm')
                    ->values(array(
                            RemoteClient::SELECT_ALGORITHM_RAND,
                            RemoteClient::SELECT_ALGORITHM_ROUND_ROBIN,
                        )
                    )
                    ->defaultValue(RemoteClient::SELECT_ALGORITHM_RAND)
                ->end()
                ->scalarNode('shared_memory')->end()
            ->append($serverNode)
            ->end()
        ;

        return $treeBuilder;
    }
}
