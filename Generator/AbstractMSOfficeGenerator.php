<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class AbstractMSOfficeGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
abstract class AbstractMSOfficeGenerator extends AbstractGenerator
{
    /**
     * WdSaveFormat Enumeration
     * @see https://msdn.microsoft.com/en-us/library/bb238158(v=office.12).aspx
     */
    const EXPORT_FORMATS = [
        self::PREVIEW_FORMAT_PDF  => 17,
        self::PREVIEW_FORMAT_HTML => 8,
        self::PREVIEW_FORMAT_TEXT => 2,
    ];

    // Mime-types allowed to convert
    const ALLOWED_INPUT_FORMATS = [];

    /**
     * @param \SplFileObject $file
     * @param string $out_format
     * @return bool
     */
    public function support(\SplFileObject $file, $out_format): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return false;
        }

        $mime_type = mime_content_type($file->getRealPath());

        return (in_array($mime_type, self::ALLOWED_INPUT_FORMATS) && (key_exists($out_format, self::EXPORT_FORMATS) || $this->isImage($out_format)));
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

        if(!$this->isImage($out_format)) {
            // Convert to output format directly
            $format_code = self::EXPORT_FORMATS[$out_format];
            $this->convert($file->getRealPath(), $preview_path, $format_code);
        } else {
            // Convert to PDF -> to image
            $format_code = self::EXPORT_FORMATS[self::PREVIEW_FORMAT_PDF];
            $out_path = $file->getRealPath() . '.' . self::PREVIEW_FORMAT_PDF;

            $this->convert($file->getRealPath(), $out_path, $format_code);

            $this->generateImagePreview($out_path.'['.$this->page_range.']', $preview_path, self::PDF_RESOLUTION);
            // Remove temp files
            if(file_exists($out_path)){
                unlink($out_path);
            }
        }

        return new \SplFileObject($preview_path);
    }

    /**
     * @param string $orig_path
     * @param string $out_path
     * @param string $format_code
     * @return void
     */
    abstract protected function convert($orig_path, $out_path, $format_code);
}