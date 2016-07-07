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
        $fileout_pdf = tempnam(sys_get_temp_dir(), $orig_path ) . '.pdf';
        $fileout_preview = $orig_path . '.' . $this->out_format;

        if(!in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS)) {
            $word = new \COM("Word.Application") or die ("Невозможно создать COM объект");
            $word->Documents->Open($orig_path, false, true);

            //$word->ActiveDocument->SaveAs($fileout, 8);
            $word->ActiveDocument->ExportAsFixedFormat($fileout_pdf, 17, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
            $word->Quit();

            if($this->out_format == self::PREVIEW_FORMAT_PDF){
                return new \SplFileObject($fileout_pdf);
            }
        }
        else{
            $fileout_pdf = $orig_path;
        }

        $im = new \Imagick();

        //$im->setResolution($this->thumbnail_width, $this->thumbnail_width);
        $im->setCompressionQuality($this->quality);
        $im->readimage($fileout_pdf.'[0]');
        $im->setImageFormat($this->out_format);
        $im->writeImage($fileout_preview);
        $im->clear();
        $im->destroy();

        $fileout_preview = $this->postProcess($fileout_preview);

        return new \SplFileObject($fileout_preview);
    }
}