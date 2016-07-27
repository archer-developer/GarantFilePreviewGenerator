<?php

namespace Garant\FilePreviewGeneratorBundle\DependencyInjection;

use Garant\FilePreviewGeneratorBundle\Client\RemoteClient;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class GarantFilePreviewGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('garant_file_preview_generator.servers', $config['servers']);
        $container->setParameter('garant_file_preview_generator.server_select_algorithm', $config['server_select_algorithm']);

        if($config['server_select_algorithm'] == RemoteClient::SELECT_ALGORITHM_ROUND_ROBIN){
            if(!empty($config['shared_memory'])){
                $container->setParameter('garant_file_preview_generator.shared_memory', $config['shared_memory']);
            }
            else{
                throw new InvalidConfigurationException('You must set shared_memory parameter if you use round_robin algorithm');
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
