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

    }
}