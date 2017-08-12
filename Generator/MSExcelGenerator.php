<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;

/**
 * Class MSExcelGenerator
 * @package Garant\FilePreviewGeneratorBundle\Generator
 */
class MSExcelGenerator extends AbstractMSOfficeGenerator
{
    /**
     * WdSaveFormat Enumeration
     * @see https://msdn.microsoft.com/en-us/library/bb241296(v=office.12).aspx
     * @see https://msdn.microsoft.com/en-us/vba/excel-vba/articles/xlfileformat-enumeration-excel
     */
    const EXPORT_FORMATS = [
        self::PREVIEW_FORMAT_PDF  => 0,
        self::PREVIEW_FORMAT_HTML => 44, // xlHtml
        self::PREVIEW_FORMAT_TEXT => 42, // xlUnicodeText
    ];

    // Mime-types allowed to convert
    const ALLOWED_INPUT_FORMATS = [
        'application/vnd.ms-excel', // xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
    ];

   /**
     * @inheritdoc
     */
    protected function convert($orig_path, $out_path, $format_code)
    {
        $this->logger->debug('COM object building');

        $excel = new \COM("Excel.Application");
        if(!$excel){
            throw new \RuntimeException('COM object not created!');
        }
        $this->logger->debug('Success');
        $this->logger->debug('Open document');
        try {

            $excel->DisplayAlerts = new \VARIANT(false, VT_BOOL);
            $excel->Workbooks->Open($orig_path, false, true);

            if ($format_code != self::EXPORT_FORMATS[self::PREVIEW_FORMAT_PDF]) {

                $this->logger->debug('Save document as ' . $out_path);

                $excel->ActiveWorkbook->SaveAs(new \VARIANT($out_path, VT_BSTR), new \VARIANT($format_code, VT_I4));
            } else {

                $this->logger->debug('ExportAsFixedFormat ' . $out_path . ' as ' . $format_code);
                //@todo Use range of pages (https://msdn.microsoft.com/en-us/library/bb243314(v=office.12).aspx)
                $excel->ActiveWorkbook->ExportAsFixedFormat($format_code, $out_path);
            }

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
            $excel->Workbooks->Close();
            $excel->Quit();
            // Release resource
            $excel = null;
        }
    }
}