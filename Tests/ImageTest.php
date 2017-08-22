<?php

/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 10.08.2017
 * Time: 21:53.
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;

class ImageTest extends AbstractGeneratorTest
{
    public function testJPEGToJPEG()
    {
        $this->generate('test.jpeg', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testGIFToJPEG()
    {
        $this->generate('test.gif', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testBMPToJPEG()
    {
        $this->generate('test.bmp', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testTIFToJPEG()
    {
        $this->generate('test.tif', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testPNGToJPEG()
    {
        $this->generate('test.png', AbstractGenerator::PREVIEW_FORMAT_JPEG);
    }

    public function testJPEGToPDF()
    {
        $this->generate('test.jpeg', AbstractGenerator::PREVIEW_FORMAT_PDF);
    }

    public function testBMPToPDF()
    {
        $this->generate('test.bmp', AbstractGenerator::PREVIEW_FORMAT_PDF);
    }

    public function testPNGToPDF()
    {
        $this->generate('test.png', AbstractGenerator::PREVIEW_FORMAT_PDF);
    }
}
