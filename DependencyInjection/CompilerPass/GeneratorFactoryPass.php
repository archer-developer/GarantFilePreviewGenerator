<?php

/**
 * Created by PhpStorm.
 * User: archer
 * Date: 07.08.2017
 * Time: 20:26
 */

namespace Garant\FilePreviewGeneratorBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GeneratorFactoryPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        $generators = $this->findAndSortTaggedServices('garant_file_preview_generator.generator', $container);
        $factoryService = $container->getDefinition('garant_file_preview_generator.generator_factory');

        foreach($generators as $serviceReference) {
            $factoryService->addMethodCall('addGenerator', $serviceReference);
        }
    }
}