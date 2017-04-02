<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 6.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class MSOfficeGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
class MSOfficeGenerator extends AbstractOfficeGenerator
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
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/pjpeg', // JPEG
        'image/vnd.microsoft.icon', // ICO
    ];

    /**
     * @inheritdoc
     */
    protected function convert($orig_path, $out_format)
    {
        $format_code = self::EXPORT_FORMATS[$out_format];
        if(!$format_code){
            throw new \InvalidArgumentException('Incorrect output format: ' . $out_format);
        }

        $out_path = $orig_path . '.' . $out_format;

        $this->output->debug('Create COM object ', false);

        $word = new \COM("Word.Application");
        if(!$word){
            throw new \RuntimeException('COM object not created!');
        }
        $this->output->debug('success');
        $this->output->debug('Open document');
        try {
            if ($out_format != self::PREVIEW_FORMAT_PDF) {

                $word->Documents->Open($orig_path, false, true);
                $this->output->debug('Save document as ' . $out_path);
                $word->ActiveDocument->SaveAs2($out_path, $format_code);
            } else {

                $mime_type = mime_content_type($orig_path);
                if(!in_array($mime_type, self::ALLOWED_INPUT_FORMATS)){
                    throw new \RuntimeException('Not allowed input format: ' . $mime_type);
                }
                $word->Documents->Open($orig_path, false, true);

                $this->output->debug('ExportAsFixedFormat ' . $out_path . ' as ' . $format_code);
                //@todo Use range of pages (https://msdn.microsoft.com/en-us/library/bb243314(v=office.12).aspx)
                $word->ActiveDocument->ExportAsFixedFormat($out_path, $format_code, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
            }

            if (!file_exists($out_path)) {
                throw new \RuntimeException('Convert failed!');
            }
        }
        finally{
            $this->output->debug('Destroy COM object');
            // Close word instance without save changes
            $word->Quit(false);
            // Release resource
            $word = null;
        }

        return $out_path;
    }
}
