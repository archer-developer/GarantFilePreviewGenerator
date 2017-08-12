<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
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
     * WdSaveFormat Enumeration
     * @see https://msdn.microsoft.com/en-us/library/bb238158(v=office.12).aspx
     */
    const EXPORT_FORMATS = [
        self::PREVIEW_FORMAT_PDF,
        self::PREVIEW_FORMAT_HTML,
        self::PREVIEW_FORMAT_TEXT,
    ];

    // Mime-types allowed to convert
    const ALLOWED_INPUT_FORMATS = [
        'plain/text',
        'text/plain',
        'text/html',
        'text/rtf',
        'application/json',
        'application/javascript',
        'application/msword', // doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
        'application/vnd.ms-excel', // xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/vnd.ms-powerpoint', // ppt
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', //pptx
    ];

    /**
     * @param \SplFileObject $file
     * @param string $out_format
     * @return bool
     */
    public function support(\SplFileObject $file, $out_format): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return false;
        }

        $mime_type = mime_content_type($file->getRealPath());

        return (in_array($mime_type, self::ALLOWED_INPUT_FORMATS) && (in_array($out_format, self::EXPORT_FORMATS) || $this->isImage($out_format)));
    }

    /**
     * @inheritdoc
     */
    public function generate(\SplFileObject $file, $out_format): \SplFileObject
    {
        $file->rewind();

        $orig_path = $file->getRealPath();
        $preview_path = $orig_path . '.' . $out_format;
        if(file_exists($preview_path)){
            unlink($preview_path);
        }

        $out_path = $orig_path . '.' . $out_format;

        $this->convert($orig_path, $out_path, $out_format);

        if($this->isImage($out_format)) {
            $preview_path = $this->generateImagePreview($out_path.'['.$this->page_range.']', $preview_path, self::PDF_RESOLUTION);
            // Remove temp files
            if(file_exists($out_path)){
                unlink($out_path);
            }
        } else {
            $preview_path = $out_path;
        }

        return new \SplFileObject($preview_path);
    }

    /**
     * @param string $orig_path
     * @param string $out_path
     * @param string $out_format
     * @return bool
     */
    protected function convert($orig_path, $out_path, $out_format)
    {
        // Generate PDF from source file
        $process = new Process("unoconv -f {$out_format} -o {$out_path} {$orig_path}");
        $process->run();
        if(!file_exists($out_path) || $process->getExitCode() > 0){
            return false;
        }

        return $out_path;
    }
}