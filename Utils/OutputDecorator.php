<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 7.8.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Utils;

use Symfony\Component\Console\Output\OutputInterface;

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
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->output->$name(...$arguments);
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

    public function writeLn($message, $newline = false, $options = 0)
    {
        $message = '<info>'.(new \DateTime())->format('h:i:s d.m.y').'</info> ' . $message;
        $this->output->write($message, $newline, $options);
    }

    /**
     * Show debug message
     * @param string $message
     * @param bool $new_line
     */
    public function debug($message, $new_line = true)
    {
        if($this->output->isDebug()) {
            if($new_line){
                $this->output->writeln($message);
            }else{
                $this->output->write($message);
            }
        }
    }
}