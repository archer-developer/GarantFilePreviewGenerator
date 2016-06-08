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
    /**
     * @inheritdoc
     */
    public function generate(\SplFileObject $file)
    {
        $file->rewind();

        $orig_path = $file->getRealPath();
        $fileout = tempnam(sys_get_temp_dir(), $orig_path ) . '.pdf';

        $word = new \COM("Word.Application") or die ("Невозможно создать COM объект");
        $word->Documents->Open($orig_path);

        //$word->ActiveDocument->SaveAs($fileout, 8);
        $word->ActiveDocument->ExportAsFixedFormat($fileout, 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
        $word->Quit();

        return new \SplFileObject($fileout);
    }
}