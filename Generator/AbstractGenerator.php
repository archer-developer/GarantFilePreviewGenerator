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
    const PREVIEW_FORMAT_HTML = 'html';

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
                $preview_path = $this->generatePreview($orig_path.'[0]', $preview_path);
            }
            // Other format to image
            else{
                $pdf_path = $this->convert($orig_path, self::PREVIEW_FORMAT_PDF);
                $preview_path = $this->generatePreview($pdf_path.'[0]', $preview_path);
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

    /**
     * @param $file_path
     * @param $preview_path
     * @return string
     */
    protected function generatePreview($file_path, $preview_path)
    {
        // Create first page screen shot
        $im = new \Imagick();

        $im->setCompressionQuality($this->quality);
        $im->readimage($file_path);
        $im->setImageFormat($this->out_format);
        $im->writeImage($preview_path);
        $im->clear();
        $im->destroy();

        $preview_path = $this->postProcess($preview_path);

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