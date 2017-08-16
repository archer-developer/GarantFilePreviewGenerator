<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 16.8.17
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class MSPowerPointGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
class MSPowerPointGenerator extends AbstractMSOfficeGenerator
{
    /**
     * WdSaveFormat Enumeration
     * @see https://msdn.microsoft.com/ru-ru/library/office/microsoft.office.interop.powerpoint.ppfixedformattype(v=office.14).aspx
     * @see https://msdn.microsoft.com/en-us/library/microsoft.office.interop.powerpoint.ppsaveasfiletype.aspx
     */
    const EXPORT_FORMATS = [
        self::PREVIEW_FORMAT_PDF  => 32,
        self::PREVIEW_FORMAT_HTML => 11, // Html
    ];

    // Mime-types allowed to convert
    const ALLOWED_INPUT_FORMATS = [
        'application/vnd.ms-powerpoint', // ppt
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', //pptx
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow', //ppsx
    ];

   /**
     * @inheritdoc
     */
    protected function convert($orig_path, $out_path, $format_code)
    {
        $this->logger->debug('COM object building');

        $powerpoint = new \COM("Powerpoint.Application");
        if(!$powerpoint){
            throw new \RuntimeException('COM object not created!');
        }
        $this->logger->debug('Success');
        $this->logger->debug('Open document');
        try {
            $powerpoint->DisplayAlerts = new \VARIANT(false, VT_BOOL);
            $powerpoint->Presentations->Open($orig_path, true, false);

            $this->logger->debug('Save document as ' . $out_path);

            $powerpoint->ActivePresentation->SaveAs(new \VARIANT($out_path, VT_BSTR), new \VARIANT($format_code, VT_I4));

            if (!file_exists($out_path)) {
                throw new \RuntimeException('Convert failed!');
            }
        }
        catch(\Throwable $e) {
            $class = get_class($e);
            throw new $class(iconv('CP1251', 'UTF-8', $e->getMessage()."\nFormat code:".$format_code."\nInput: ".$orig_path."\nOutput: ".$out_path));
        }
        finally{
            $this->logger->debug('Destroy COM object');
            // Close word instance without save changes
            //$powerpoint->Presentations->Close();
            $powerpoint->Quit();
            // Release resource
            $powerpoint = null;
        }
    }
}