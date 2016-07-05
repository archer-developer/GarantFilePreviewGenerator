<?php

namespace Garant\FilePreviewGeneratorBundle\Command;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class GarantFilePreviewGeneratorServerStartCommand
 * @package Garant\FilePreviewGeneratorBundle\Command
 */
class GarantFilePreviewGeneratorServerStartCommand extends ContainerAwareCommand
{
    const BUFFER_SIZE = 262144; // 256Kb

    protected function configure()
    {
        $this
            ->setName('garant:file-preview-generator:server-start')
            ->setDescription('Start generator server in 127.0.0.1')
            ->addArgument('server', InputArgument::REQUIRED, 'Server name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');
        $io = new SymfonyStyle($input, $cliOutput = $output);

        $server = $input->getArgument('server');
        $availableServers = $this->getContainer()->getParameter('garant_file_preview_generator.servers');
        if(!isset($availableServers[$server])){
            $io->error('Server "' . $server . '" is not configured');
            return;
        }

        $loop = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Server($loop);
        $http = new \React\Http\Server($socket);

        $http->on('request', function(\React\Http\Request $request, \React\Http\Response $response) use ($io){

            $io->write('Client accepted: ');

            $files = $request->getFiles();
            if(empty($files['file'])){
                $io->error("Empty file");
                return $this->error($response, "Empty file");
            }

            // Copy file from memory to temp
            $tmp_name = tempnam(sys_get_temp_dir(), 'preview_attachment_');
            if(isset($request->getPost()['file_name'])){

                $io->write($request->getPost()['file_name'], true);

                preg_match('/\.([^\.]+)$/', $request->getPost()['file_name'], $extension);
                if(isset($extension[1])){
                    $tmp_name .= '.' . $extension[1];
                }
            }
            $temp_file = new \SplFileObject($tmp_name, 'w');
            while(!feof($files['file']['stream'])){
                $temp_file->fwrite(fread($files['file']['stream'], self::BUFFER_SIZE));
            }

            $out_format = AbstractGenerator::PREVIEW_FORMAT_JPEG;
            if(!isset($request->getPost()['out_format'])){
                $out_format = $request->getPost()['out_format'];
            }

            try{
                // Configure generator
                $generator = $this->getContainer()->get('garant_file_preview_generator.libreoffice_generator');
                $generator->setOutFormat($out_format);
                if(isset($request->getPost()['quality'])){
                    $generator->setQuality($request->getPost()['quality']);
                }
                if(isset($request->getPost()['filter'])){
                    $generator->setFilter($request->getPost()['filter']);
                }

                $preview = $generator->generate($temp_file);
                if(!$preview){
                    return $this->error($response, "Conversion error");
                }
            }
            catch(\Throwable $e){
                return $this->error($response, $e->getMessage());
            }

            $statusCode = 200;
            $headers = array('Content-Type: application/octet-stream');

            $response->writeHead($statusCode, $headers);
            while(!$preview->eof()){
                $response->write($preview->fread(self::BUFFER_SIZE));
            }
            $response->end();
        });

        $socket->listen($availableServers[$server]['port']);
        $io->success('Preview generator is started on port ' . $availableServers[$server]['port']);

        $loop->run();

        $io->comment('Server is stopped');
    }

    /**
     * @param \React\Http\Response $response
     * @param $message
     * @return bool
     * @throws \Exception
     */
    protected function error(\React\Http\Response $response, $message)
    {
        $response->writeHead(500, array('Content-Type: text/plain'));
        $response->end($message);

        return false;
    }
}
