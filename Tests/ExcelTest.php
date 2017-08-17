<?php

/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 10.08.2017
 * Time: 21:53.
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;

class ExcelTest extends AbstractGeneratorTest
{
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
}
