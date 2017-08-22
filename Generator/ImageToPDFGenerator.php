<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58.
 */

namespace Garant\FilePreviewGeneratorBundle\Generator;
use Symfony\Component\Process\Process;

/**
 * Class ImageToPDFGenerator.
 */
class ImageToPDFGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    public function support(\SplFileObject $file, $out_format): bool
    {
        return $this->isImage($file->getExtension()) && $this->isPDF($out_format);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(\SplFileObject $file, $out_format): \SplFileObject
    {
        $file->rewind();

        $preview_path = $this->generatePreviewPath($file, $out_format);

        // Create page range screen shot
        $convert_cmd = "convert \"{$file->getRealPath()}\" \"{$preview_path}\"";

        $this->logger->debug($convert_cmd);

        $process = new Process($convert_cmd);
        $process->run();
        if (!file_exists($preview_path) || $process->getExitCode() > 0) {
            $this->logger->debug('Error. Exit code: '.$process->getExitCode());
            throw new \RuntimeException('Cannot create PDF from image');
        }

        return new \SplFileObject($preview_path);
    }
}
