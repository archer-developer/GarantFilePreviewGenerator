<?php
/**
 * Created by PhpStorm.
 * User: archer
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

use Symfony\Component\Process\Process;

/**
 * Class LibreOfficeGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
class LibreOfficeGenerator extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function generate(\SplFileObject $file)
    {
        $file->rewind();

        $orig_path = $file->getRealPath();
        $pdf_path = $orig_path . '.pdf';
        $preview_path = $orig_path . '.' . $this->out_format;

        if(!in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS)){
            // Generate PDF from source file
            $process = new Process("unoconv -f pdf -o {$pdf_path} {$orig_path}");
            $process->run();
            if(!file_exists($pdf_path) || $process->getExitCode() > 0){
                return false;
            }

            if($this->out_format == self::PREVIEW_FORMAT_PDF){
                return new \SplFileObject($pdf_path);
            }
        }
        else{
            $pdf_path = $orig_path;
        }

        // Create first page screen shot
        $im = new \Imagick();

        //$im->setResolution($this->thumbnail_width, $this->thumbnail_width);
        $im->setCompressionQuality($this->quality);
        $im->readimage($pdf_path.'[0]');
        $im->setImageFormat($this->out_format);
        $im->writeImage($preview_path);
        $im->clear();
        $im->destroy();

        // Remove temp files
        if(file_exists($pdf_path)){
            unlink($pdf_path);
        }

        $preview_path = $this->postProcess($preview_path);

        return new \SplFileObject($preview_path);
    }
}