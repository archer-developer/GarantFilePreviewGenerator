<?php
/**
 * Created by PhpStorm.
 * User: archer
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class AbstractOfficeGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
abstract class AbstractOfficeGenerator extends AbstractGenerator
{
    /**
     * Generate file preview in required format
     *
     * @param \SplFileObject $file
     * @return \SplFileObject
     */
    public function generate(\SplFileObject $file)
    {
        $file->rewind();

        $orig_path = $file->getRealPath();
        $preview_path = $orig_path . '.' . $this->out_format;

        // Convert to image
        if($this->isImage($this->out_format)){

            // Image to image
            if($this->isImage($file->getExtension())){
                $preview_path = $this->generatePreview($orig_path, $preview_path);
            }
            // PDF to image
            elseif($this->isPDF($file->getExtension())){
                $preview_path = $this->generatePreview($orig_path.'[0]', $preview_path, self::PDF_RESOLUTION);
            }
            // Other format to image
            else{
                $pdf_path = $this->convert($orig_path, self::PREVIEW_FORMAT_PDF);
                $preview_path = $this->generatePreview($pdf_path.'[0]', $preview_path, self::PDF_RESOLUTION);
                // Remove temp files
                if(file_exists($pdf_path)){
                    unlink($pdf_path);
                }
            }

            return new \SplFileObject($preview_path);
        }
        // Convert to PDF
        elseif($this->isPdf($this->out_format)){
            $pdf_path = $this->convert($orig_path, self::PREVIEW_FORMAT_PDF);
            return new \SplFileObject($pdf_path);
        }

        // Convert something to something
        $preview_path = $this->convert($orig_path, $this->out_format);

        return new \SplFileObject($preview_path);
    }

    /**
     * Convert something to something
     *
     * @param $orig_path
     * @param $out_format
     * @return mixed
     */
    abstract protected function convert($orig_path, $out_format);
}