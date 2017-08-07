<?php

namespace Garant\FilePreviewGeneratorBundle;

use Garant\FilePreviewGeneratorBundle\DependencyInjection\CompilerPass\GeneratorFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GarantFilePreviewGeneratorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GeneratorFactoryPass());
    }
}
