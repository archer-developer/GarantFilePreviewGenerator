<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class PDFToImageGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
class PDFToImageGenerator extends AbstractGenerator
{
    /**
     * @inheritdoc
     */
    public function support(\SplFileObject $file, $out_format): bool
    {
        return ($this->isPDF($file->getExtension()) && $this->isImage($out_format));
    }

    /**
     * Generate file preview in required format
     *
     * @param \SplFileObject $file
     * @param string $out_format
     * @return \SplFileObject
     */
    public function generate(\SplFileObject $file, $out_format)
    {
        $file->rewind();

        $preview_path = $this->generatePreviewPath($file, $out_format);
        $this->generateImagePreview($file->getRealPath().'['.$this->page_range.']', $preview_path, self::PDF_RESOLUTION);

        return new \SplFileObject($preview_path);
    }
}