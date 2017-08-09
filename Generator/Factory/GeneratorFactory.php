<?php

/**
 * Created by PhpStorm.
 * User: archer
 * Date: 07.08.2017
 * Time: 20:16
 */

namespace Garant\FilePreviewGeneratorBundle\Generator\Factory;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;

class GeneratorFactory
{
    /**
     * @var AbstractGenerator[]
     */
    protected $generators;

    /**
     * @param AbstractGenerator $generator
     */
    public function addGenerator(AbstractGenerator $generator)
    {
        $this->generators[] = $generator;
    }

    /**
     * @param \SplFileObject $file
     * @param string $out_format
     *
     * @return AbstractGenerator|null
     */
    public function get(\SplFileObject $file, $out_format): ?AbstractGenerator
    {
        foreach($this->generators as $generator) {
            if($generator->support($file, $out_format)) {
                return clone $generator;
            }
        }

        return null;
    }
}