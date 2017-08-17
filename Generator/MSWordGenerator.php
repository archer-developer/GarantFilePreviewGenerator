<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58.
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class MSWordGenerator.
 */
class MSWordGenerator extends AbstractMSOfficeGenerator
{
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
    ];

    /**
     * {@inheritdoc}
     */
    protected function convert($orig_path, $out_path, $format_code)
    {
        $this->logger->debug('COM object building');

        $word = new \COM('Word.Application');
        if (!$word) {
            throw new \RuntimeException('COM object not created!');
        }
        $this->logger->debug('Success');
        $this->logger->debug('Open document');
        try {
            $word->DisplayAlerts = new \VARIANT(false, VT_BOOL);

            if ($format_code != self::EXPORT_FORMATS[self::PREVIEW_FORMAT_PDF]) {
                $word->Documents->Open($orig_path, false, true);
                $this->logger->debug('Save document as '.$out_path);

                $word->ActiveDocument->SaveAs2(new \VARIANT($out_path, VT_BSTR), new \VARIANT($format_code, VT_I4));
            } else {
                $word->Documents->Open($orig_path, false, true);

                $this->logger->debug('ExportAsFixedFormat '.$out_path.' as '.$format_code);
                //@todo Use range of pages (https://msdn.microsoft.com/en-us/library/bb243314(v=office.12).aspx)
                $word->ActiveDocument->ExportAsFixedFormat($out_path, $format_code, false, 0, 0, 0, 0, 7, true, true, 2, true, true, false);
            }

            if (!file_exists($out_path)) {
                throw new \RuntimeException('Convert failed!');
            }
        } catch (\Throwable $e) {
            $class = get_class($e);
            throw new $class(iconv('CP1251', 'UTF-8', $e->getMessage()."\nFormat code:".$format_code."\nInput: ".$orig_path."\nOutput: ".$out_path));
        } finally {
            $this->logger->debug('Destroy COM object');
            // Close word instance without save changes
            $word->Quit(false);
            // Release resource
            $word = null;
        }
    }
}
