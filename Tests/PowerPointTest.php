<?php

/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 10.08.2017
 * Time: 21:53.
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;

class PowerPointTest extends AbstractGeneratorTest
{
    public function setUp()
    {
        parent::setUp();

        $this->markTestSkipped(
            'PowerPoint generator does not complete yet'
        );
    }

    public function testPPTToJPEG()
    {
        $this->generate('test.ppt', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testPPTToPDF()
    {
        $this->generate('test.ppt', AbstractGenerator::PREVIEW_FORMAT_PDF);
    }

    public function testPPTXToPDF()
    {
        $this->generate('test.pptx', AbstractGenerator::PREVIEW_FORMAT_PDF);
    }

    public function testPPTXToJPEG()
    {
        $this->generate('test.pptx', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }
}
