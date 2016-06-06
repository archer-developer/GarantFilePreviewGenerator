<?php
/**
 * Created by PhpStorm.
 * User: archer
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class AbstractGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
abstract class AbstractGenerator
{
    const PREVIEW_FORMAT_JPEG = 'jpeg';
    const PREVIEW_FORMAT_PNG  = 'png';

    // Preview output format
    protected $out_format = self::PREVIEW_FORMAT_JPEG;

    // JPEG quality
    protected $quality = 100;

    protected $thumbnail_width = 1200;

    // Skip to pdf converting
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];

    /**
     * @param \SplFileObject $file
     * @return \SplFileObject
     */
    abstract public function generate(\SplFileObject $file);

    /**
     * @param $quality
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * @param $width
     */
    public function setThumbnailWidth($width)
    {
        $this->thumbnail_width = $width;
    }

    /**
     * @param string $format
     */
    public function setOutFormat($format)
    {
        $this->out_format = $format;
    }
}