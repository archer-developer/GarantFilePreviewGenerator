<?php
/**
 * Created by PhpStorm.
 * User: archer
 * Date: 6.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class MSOfficeGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
class MSOfficeGenerator extends AbstractGenerator
{
    const MS_FORMATS = [
        self::PREVIEW_FORMAT_PDF => 17,
    ];

    /**
     * @inheritdoc
     */
    protected function convert($orig_path, $out_format)
    {
        $format_code = self::MS_FORMATS[$out_format];
        if(!$format_code){
            throw new \InvalidArgumentException('Incorrect output format: ' . $out_format);
        }

        $out_path = $orig_path . '.' . $out_format;

        $word = new \COM("Word.Application");
        if(!$word){
            throw new \RuntimeException('COM object not created!');
        }
        $word->Documents->Open($orig_path, false, true);

        //$word->ActiveDocument->SaveAs($fileout, 8);
        $word->ActiveDocument->ExportAsFixedFormat($out_path, 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
        $word->Quit();

        return $out_path;
    }
}