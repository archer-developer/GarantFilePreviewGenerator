<?php

/**
 * Created by PhpStorm.
 * User: archer
 * Date: 10.08.2017
 * Time: 21:53
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;
use Garant\FilePreviewGeneratorBundle\Generator\Factory\GeneratorFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GeneratorTest extends WebTestCase
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

    public function testImageToImage()
    {
        $this->generate('test.jpeg', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testXLSXToTXT()
    {
        $this->generate('test.xlsx', AbstractGenerator::PREVIEW_FORMAT_TEXT);
    }

    public function testXLSToJPEG()
    {
        $this->generate('test.xls', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testXLSXToJPEG()
    {
        $this->generate('test.xlsx', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testXLSXToHTML()
    {
        $this->generate('test.xlsx', AbstractGenerator::PREVIEW_FORMAT_HTML);
    }

    public function testPlainToJPEG()
    {
        $this->generate('test.txt', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testDocxToJPEG()
    {
        $this->generate('test.docx', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testPDFToJPEG()
    {
        $this->generate('test.pdf', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testDocxToPDF()
    {
        $this->generate('test.docx', AbstractGenerator::PREVIEW_FORMAT_PDF);
    }

    public function testDocxToHTML()
    {
        $this->generate('test.docx', AbstractGenerator::PREVIEW_FORMAT_HTML);
    }

    public function testRTFToHTML()
    {
        $this->generate('test.rtf', AbstractGenerator::PREVIEW_FORMAT_HTML);
    }

    public function testDocxToPlain()
    {
        $this->generate('test.docx', AbstractGenerator::PREVIEW_FORMAT_TEXT);
    }

    private function generate($fileName, $format): \SplFileObject
    {
        $temp_file = new \SplFileObject(__DIR__.'/files/'.$fileName);

        $generator = $this->factory->get($temp_file, $format);
        $preview_file = $generator->generate($temp_file, $format);

        $this->assertInstanceOf(\SplFileObject::class, $preview_file);
        $this->assertGreaterThan(0, $preview_file->getSize());

        return $preview_file;
    }
}