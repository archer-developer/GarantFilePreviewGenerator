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
class LibreOfficeGenerator extends AbstractOfficeGenerator
{
    protected function convert($orig_path, $out_format)
    {
        $out_path = $orig_path . '.' . $out_format;

        // Generate PDF from source file
        $process = new Process("unoconv -f {$out_format} -o {$out_path} {$orig_path}");
        $process->run();
        if(!file_exists($out_path) || $process->getExitCode() > 0){
            return false;
        }

        return $out_path;
    }
}