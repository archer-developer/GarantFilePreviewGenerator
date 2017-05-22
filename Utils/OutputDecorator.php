<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 7.8.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Utils;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class OutputDecorator
 * @package Garant\FilePreviewGeneratorBundle\Utils
 */
class OutputDecorator
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * AbstractGenerator constructor.
     * @param SymfonyStyle $output
     */
    public function __construct(SymfonyStyle $output)
    {
        $this->output = $output;
    }

    public function __call($method, $args)
    {
        $this->output->$method(...$args);
    }

    /**
     * Show current memory usage
     */
    public function logMemoryUsage()
    {
        if($this->output->isDebug()) {
            $this->output->writeln('<info>Memory usage: ' . number_format(memory_get_usage() / 1024 / 1024, 2) . 'Mb</info>');
            $this->output->writeln('<info>Memory peak usage: ' . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . 'Mb</info>');
        }
    }

    /**
     * Show debug message
     * @param string $message
     * @param bool $new_line
     */
    public function debug($message, $new_line = true)
    {
        if($this->output->isDebug()) {
            $this->output->write($message, $new_line);
        }
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->output->isDebug();
    }
}