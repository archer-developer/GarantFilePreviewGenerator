<?php
/**
 * Created by PhpStorm.
 * User: archer
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;

/**
 * Class AbstractGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
abstract class AbstractGenerator
{
    const PREVIEW_FORMAT_JPEG = 'jpeg';
    const PREVIEW_FORMAT_PNG  = 'png';
    const PREVIEW_FORMAT_PDF  = 'pdf';

    // Preview output format
    protected $out_format = self::PREVIEW_FORMAT_JPEG;

    // JPEG quality
    protected $quality = 100;

    // LiipImagine filter to post processing
    protected $filter;

    // Base preview width in pixels
    protected $thumbnail_width = 800;

    /**
     * @var FilterManager $filter_manager
     */
    protected $filter_manager;

    /**
     * @var LoaderInterface
     */
    protected $binary_loader;

    // Skip to pdf converting
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];

    /**
     * @param \SplFileObject $file
     * @return \SplFileObject
     */
    abstract public function generate(\SplFileObject $file);

    /**
     * AbstractGenerator constructor.
     * @param FilterManager $filter_manager
     * @param LoaderInterface $binary_loader
     */
    public function __construct(FilterManager $filter_manager, LoaderInterface $binary_loader)
    {
        $this->filter_manager = $filter_manager;
        $this->binary_loader = $binary_loader;
    }

    /**
     * @param $quality
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * Set LiipImagine filter to post process
     * @param $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param string $format
     */
    public function setOutFormat($format)
    {
        $this->out_format = $format;
    }

    /**
     * Process preview image
     *
     * @param string $path - absolute path to preview image
     * @return string
     */
    protected function postProcess($path)
    {
        if(!$this->filter){
            return $path;
        }

        /**
         * @var Binary $binary
         */
        $binary = $this->binary_loader->find($path);
        $binary = $this->filter_manager->applyFilter($binary, $this->filter);
        file_put_contents($path, $binary->getContent());

        return $path;
    }
}