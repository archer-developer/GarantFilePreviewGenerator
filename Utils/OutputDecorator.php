<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 7.8.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Utils;

use Symfony\Bridge\Monolog\Logger;
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
     * @var Logger
     */
    protected $logger;

    /**
     * AbstractGenerator constructor.
     * @param SymfonyStyle $output
     * @param Logger $logger
     */
    public function __construct(SymfonyStyle $output, Logger $logger)
    {
        $this->output = $output;
        $this->logger = $logger;
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
     * @param $message
     * @param bool $newline
     * @param int $options
     */
    public function writeLn($message, $newline = false, $options = 0)
    {
        $out_message = '<info>'.(new \DateTime())->format('h:i:s d.m.y').'</info> ' . $message;
        $this->output->write($out_message, $newline, $options);
        $this->logger->info($message);
    }

    /**
     * @param $message
     * @param bool $newline
     * @param int $options
     */
    public function error($message, $newline = false, $options = 0)
    {
        $out_message = '<error>'.(new \DateTime())->format('h:i:s d.m.y').'</error> ' . $message;
        $this->output->write($out_message, $newline, $options);
        $this->logger->err($message);
    }

    /**
     * @param $message
     */
    public function success($message)
    {
        $this->output->success($message);
        $this->logger->info($message);
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

            $this->logger->debug($message);
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