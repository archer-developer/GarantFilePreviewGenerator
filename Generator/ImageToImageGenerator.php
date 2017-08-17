<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58.
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class ImageToImageGenerator.
 */
class ImageToImageGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    public function support(\SplFileObject $file, $out_format): bool
    {
        return $this->isImage($file->getExtension()) && $this->isImage($out_format);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(\SplFileObject $file, $out_format): \SplFileObject
    {
        $file->rewind();

        $preview_path = $this->generatePreviewPath($file, $out_format);
        $this->generateImagePreview($file->getRealPath(), $preview_path);

        return new \SplFileObject($preview_path);
    }
}
