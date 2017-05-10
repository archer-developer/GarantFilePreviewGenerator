<?php

namespace Garant\FilePreviewGeneratorBundle\Command;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;
use Garant\FilePreviewGeneratorBundle\Utils\OutputDecorator;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\Http\Server;
use React\Promise\Promise;
use React\Stream\ReadableStream;
use React\Tests\Stream\ReadableStreamTest;
use Symfony\Bridge\Monolog\Logger;
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

    /**
     * @var OutputDecorator $io
     */
    protected $io;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $server;

    protected function configure()
    {
        $this
            ->setName('garant:file-preview-generator:server-start')
            ->setDescription('Start generator server')
            ->addArgument('server', InputArgument::REQUIRED, 'Server name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');
        $this->logger = $this->getContainer()->get('logger');

        $this->io = new OutputDecorator(new SymfonyStyle($input, $cliOutput = $output));

        $this->server = $input->getArgument('server');
        $availableServers = $this->getContainer()->getParameter('garant_file_preview_generator.servers');
        if(!isset($availableServers[$this->server])){
            $this->logger->error('Server "' . $this->server . '" is not configured');
            return;
        }

        $loop = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Server($availableServers[$this->server]['host'] . ':' . $availableServers[$this->server]['port'], $loop);
        $http = new Server($socket, function (ServerRequestInterface $request) {

            return new Promise(function ($resolve, $reject) use ($request) {

                $this->logger->info('Client accepted at ' . date('h:i:s'));
                $this->io->logMemoryUsage();

                // Generate temp name to store file body
                $tmp_name = sys_get_temp_dir() . '/preview_attachment_' . $this->server;
                if(isset($request->getQueryParams()['file_name'])){

                    $this->logger->debug($request->getQueryParams()['file_name'], false);

                    preg_match('/\.([^\.]+)$/', $request->getQueryParams()['file_name'], $extension);
                    if(isset($extension[1])){
                        $tmp_name .= '.' . $extension[1];
                    }
                }
                $this->logger->debug('Temp name: ' . $tmp_name);

                // Open temp file
                $temp_file = new \SplFileObject($tmp_name, 'w');

                // Write data to temp file
                $request->getBody()->on('data', function ($data) use ($temp_file) {
                    $temp_file->fwrite($data);
                });

                // Convert temp file and send response to client
                $request->getBody()->on('end', function () use ($resolve, $reject, $request, $temp_file){

                    try{
                        // Select generator
                        $this->logger->debug('Select generator: ');
                        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                            $this->logger->debug('msoffice_generator');
                            $generator = $this->getContainer()->get('garant_file_preview_generator.msoffice_generator');
                        }
                        else{
                            $this->logger->debug('libreoffice_generator');
                            $generator = $this->getContainer()->get('garant_file_preview_generator.libreoffice_generator');
                        }
                        $generator->setOutput($this->io);

                        // Configure generator
                        $out_format = AbstractGenerator::PREVIEW_FORMAT_JPEG;
                        if(!empty($request->getQueryParams()['out_format'])){
                            $out_format = $request->getQueryParams()['out_format'];
                        }
                        $this->logger->debug('Set output format: ' . $out_format);
                        $generator->setOutFormat($out_format);

                        if(isset($request->getQueryParams()['quality'])){
                            $this->logger->debug('Set quality: ' . $request->getQueryParams()['quality']);
                            $generator->setQuality($request->getQueryParams()['quality']);
                        } else {
                            $generator->setQuality(AbstractGenerator::JPEG_QUALITY);
                        }

                        if(!empty($request->getQueryParams()['page_count'])){
                            $this->logger->debug('Set page count: ' . $request->getQueryParams()['page_count']);
                            $generator->setPageCount($request->getQueryParams()['page_count']);
                        } else{
                            $generator->setPageRange(AbstractGenerator::PAGE_RANGE);
                        }
                        $this->logger->debug('Page range: ' . $generator->getPageRange());

                        if(isset($request->getQueryParams()['filter'])){
                            $this->logger->debug('Set post filter: ' . $request->getQueryParams()['filter']);
                            $generator->setFilter($request->getQueryParams()['filter']);
                        } else {
                            $generator->setFilter(null);
                        }

                        $this->logger->debug('Start generation: ' . $temp_file->getRealPath());
                        $preview = $generator->generate($temp_file);
                        if(!$preview){
                            throw new \RuntimeException("Conversion error");
                        }
                    }
                    catch(\Throwable $e){
                        $this->logger->error($e->getMessage());
                        return $reject($this->error($e->getMessage()));
                    }
                    finally{
                        if($temp_file->getRealPath()){
                            $path = $temp_file->getRealPath();
                            // Close file pointer
                            $temp_file = null;
                            @unlink($path);
                        }
                    }

                    $this->logger->debug('Send preview to client');

                    $statusCode = 200;
                    $headers = array('Content-Type' => 'application/octet-stream');
                    $stream = new ReadableStream();
                    $response = new Response($statusCode, $headers, $stream);

                    while(!$preview->eof()){
                        $stream->emit('data', $preview->fread(self::BUFFER_SIZE));
                    }

                    if($preview->getRealPath()){
                        $path = $preview->getRealPath();
                        $preview = null;
                        unlink($path);
                    }

                    $this->logger->info('Client processed at ' . date('h:i:s'));

                    return $resolve($response);
                });

                // an error occures e.g. on invalid chunked encoded data or an unexpected 'end' event
                $request->getBody()->on('error', function (\Exception $exception) use ($resolve, $reject) {

                    $this->logger->error($exception->getMessage());

                    return $reject($this->error($exception->getMessage()));
                });
            });
        });

        $this->io->success(
            'Preview generator is started on port ' .
            $availableServers[$this->server]['port'] .
            ' on host ' .
            $availableServers[$this->server]['host']
        );

        $this->io->logMemoryUsage();

        try{
            $loop->run();
        }
        catch(\InvalidArgumentException $e){
            $this->logger->error("InvalidArgumentException: " . $e->getMessage());
        }

        $this->logger->info('Server is stopped');
    }

    /**
     * @param $message
     * @return Response
     * @throws \Exception
     */
    protected function error($message)
    {
        return new Response(500, array('Content-Type' => 'text/plain'), $message);
    }
}
