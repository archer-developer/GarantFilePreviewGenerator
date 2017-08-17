<?php

/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 10.08.2017
 * Time: 21:53.
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;

class Word extends AbstractGeneratorTest
{
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
}
