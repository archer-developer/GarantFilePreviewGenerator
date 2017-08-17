<?php

/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 10.08.2017
 * Time: 21:53.
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Generator\Factory\GeneratorFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractGeneratorTest extends WebTestCase
{
    /**
     * @var GeneratorFactory
     */
    private $factory;

    public function setUp()
    {
        static::bootKernel();

        $this->factory = self::$kernel->getContainer()->get('garant_file_preview_generator.generator_factory');
    }

    /**
     * @param $fileName
     * @param $format
     *
     * @return \SplFileObject
     */
    protected function generate($fileName, $format): \SplFileObject
    {
        $temp_file = new \SplFileObject(__DIR__.'/files/'.$fileName);

        $generator = $this->factory->get($temp_file, $format);
        $preview_file = $generator->generate($temp_file, $format);

        $this->assertInstanceOf(\SplFileObject::class, $preview_file);
        $this->assertGreaterThan(0, $preview_file->getSize());

        return $preview_file;
    }
}
