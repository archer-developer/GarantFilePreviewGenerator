<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Process\Process;

/**
 * Class AbstractGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
abstract class AbstractGenerator
{
    const PREVIEW_FORMAT_JPEG = 'jpeg';
    const PREVIEW_FORMAT_PNG  = 'png';
    const PREVIEW_FORMAT_PDF  = 'pdf';
    const PREVIEW_FORMAT_HTML = 'html';
    const PREVIEW_FORMAT_TEXT = 'txt';

    // Skip to pdf converting
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp'];

    // Resolution to convert vector (like PDF) to bitmap image
    const PDF_RESOLUTION = 100;

    // Default JPEG quality
    const JPEG_QUALITY = 100;

    // Default page range
    const PAGE_RANGE = '0';

    /**
     * @var string - Preview output format
     */
    protected $out_format = self::PREVIEW_FORMAT_JPEG;

    /**
     * @var int - JPEG quality
     */
    protected $quality = 100;

    /**
     * @var string - Range of pages to convert
     */
    protected $page_range = '0';

    /**
     * @var string - LiipImagine filter to post processing
     */
    protected $filter;

    /**
     * @var FilterManager $filter_manager
     */
    protected $filter_manager;

    /**
     * @var LoaderInterface
     */
    protected $binary_loader;

    /**
     * @var Logger
     */
    protected $logger;

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
     * Generate file preview in required format
     *
     * @param \SplFileObject $file
     * @return \SplFileObject
     */
    abstract public function generate(\SplFileObject $file);

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
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
     * @param string $page_range
     */
    public function setPageRange($page_range)
    {
        $this->page_range = $page_range;
    }

    /**
     * @return string
     */
    public function getPageRange()
    {
        return $this->page_range;
    }

    /**
     * Convert pages from 0 to $page_count
     * @param string $page_count
     */
    public function setPageCount($page_count)
    {
        $max_page = intval($page_count) - 1;
        $this->page_range = ($max_page > 0) ? '0-' . $max_page : '0';
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
            $this->logger->debug('Post processing: skipped');
            return $path;
        }

        /**
         * @var Binary $binary
         */
        $binary = $this->binary_loader->find($path);

        $this->logger->debug('Post processing: apply filter ' . $this->filter);

        $binary = $this->filter_manager->applyFilter($binary, $this->filter);
        file_put_contents($path, $binary->getContent());

        return $path;
    }

    /**
     * @param string $file_path
     * @param string $preview_path
     * @param integer $density
     * @return string
     */
    protected function generatePreview($file_path, $preview_path, $density = 100)
    {
        // Create page range screen shot
        $convert_cmd = "convert -density {$density} -quality {$this->quality} -background white -alpha remove -append";
        $convert_cmd = $convert_cmd . " {$file_path} " . $preview_path;

        $this->logger->debug($convert_cmd);

        $process = new Process($convert_cmd);
        $process->run();
        if(!file_exists($preview_path) || $process->getExitCode() > 0){
            $this->logger->debug('Error. Exit code: ' . $process->getExitCode());
            return false;
        }

        $this->postProcess($preview_path);

        return $preview_path;
    }

    /**
     * @param $ext
     * @return bool
     */
    protected function isImage($ext)
    {
        return in_array(strtolower($ext), self::IMAGE_EXTENSIONS);
    }

    /**
     * @param $ext
     * @return bool
     */
    protected function isPDF($ext)
    {
        return strtolower($ext) == self::PREVIEW_FORMAT_PDF;
    }
}