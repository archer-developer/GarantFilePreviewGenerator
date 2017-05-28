<?php
/**
 * Created by PhpStorm.
 * User: Samusevich Alexander
 * Date: 26.03.2017
 * Time: 22:01
 */

namespace Garant\FilePreviewGeneratorBundle\Supervisor;

use Garant\FilePreviewGeneratorBundle\Utils\OutputDecorator;
use Symfony\Component\Process\Process;

/**
 * Class Supervisor
 * @package Garant\FilePreviewGeneratorBundle\Supervisor
 */
class Supervisor implements SupervisorInterface
{
    const PROCESS_STRING = 'php bin/console garant:file-preview-generator:server-start %server%';

    /**
     * @var array
     */
    private $processes = [];

    /**
     * @var string
     */
    private $env = 'prod';

    /**
     * @param $env
     */
    public function setEnvironment($env)
    {
        $this->env = $env;
    }

    /**
     * @param array $servers - servers to run
     * @param OutputDecorator $io
     */
    public function run(array $servers, OutputDecorator $io)
    {
        $iteration_counter = 0;
        while(true){
            $need_to_start = array_keys($servers);

            /**
             * @var ChildProcess $process
             */
            foreach($this->processes as $key => $process){

                if($process->process->isRunning()){
                    unset($need_to_start[array_search($process->server, $need_to_start)]);
                }
                else{
                    $debug_message = "Process ({$process->server}) was terminated: " . $process->process->getErrorOutput();
                    $io->error($debug_message, true);
                    unset($this->processes[$key]);
                }
            }

            foreach($need_to_start as $server){

                $cmd = str_replace("%server%", $server, self::PROCESS_STRING);
                $cmd .= ' --env=' . $this->env;
                if($io->isDebug()) {
                    $cmd .= ' -vvv';
                }
                $process = new Process($cmd);
                try{
                    $process->start();

                    $childProcess = new ChildProcess();
                    $childProcess->server = $server;
                    $childProcess->bornTime = new \DateTime();
                    $childProcess->process = $process;

                    $this->processes[] = $childProcess;

                    $io->success("Process {$server} started", true);
                }
                catch(\RuntimeException $e){
                    $io->error("Process {$server} start error: " . $e->getMessage(), true);
                }
            }

            sleep(1);
            $iteration_counter++;

            if(!($iteration_counter % 60)){
                $io->logMemoryUsage();
            }
        }
    }
}