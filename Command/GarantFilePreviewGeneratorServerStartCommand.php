<?php

namespace Garant\FilePreviewGeneratorBundle\Command;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;
use Garant\FilePreviewGeneratorBundle\Utils\OutputDecorator;
use React\Tests\Stream\ReadableStreamTest;
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
        $this->io = new OutputDecorator(new SymfonyStyle($input, $cliOutput = $output));

        $server = $input->getArgument('server');
        $availableServers = $this->getContainer()->getParameter('garant_file_preview_generator.servers');
        if(!isset($availableServers[$server])){
            $this->io->error('Server "' . $server . '" is not configured');
            return;
        }

        $loop = \React\EventLoop\Factory::create();
        $socket = new \React\Socket\Server($loop);
        $http = new \React\Http\Server($socket);

        $http->on('request', function(\React\Http\Request $request, \React\Http\Response $response){

            $this->io->writeLn('Client accepted at ' . date('h:i:s'));
            $this->io->logMemoryUsage();

            $files = $request->getFiles();
            if(empty($files['file'])){
                $this->io->error("Empty file");
                return $this->error($response, "Empty file");
            }

            // Copy file from memory to temp
            $tmp_name = tempnam(sys_get_temp_dir(), 'preview_attachment_');
            if(isset($request->getPost()['file_name'])){

                $this->io->write($request->getPost()['file_name'], true);

                preg_match('/\.([^\.]+)$/', $request->getPost()['file_name'], $extension);
                if(isset($extension[1])){
                    $tmp_name .= '.' . $extension[1];
                }
            }
            $temp_file = new \SplFileObject($tmp_name, 'w');
            while(!feof($files['file']['stream'])){
                $temp_file->fwrite(fread($files['file']['stream'], self::BUFFER_SIZE));
            }

            try{
                // Select generator
                $this->io->debug('Select generator: ', false);
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $this->io->debug('msoffice_generator');
                    $generator = $this->getContainer()->get('garant_file_preview_generator.msoffice_generator');
                }
                else{
                    $this->io->debug('libreoffice_generator');
                    $generator = $this->getContainer()->get('garant_file_preview_generator.libreoffice_generator');
                }
                $generator->setOutput($this->io);

                // Configure generator
                $out_format = AbstractGenerator::PREVIEW_FORMAT_JPEG;
                if(!empty($request->getPost()['out_format'])){
                    $this->io->debug('Set output format: ' . $request->getPost()['out_format']);
                    $out_format = $request->getPost()['out_format'];
                }
                $generator->setOutFormat($out_format);

                if(isset($request->getPost()['quality'])){
                    $this->io->debug('Set quality: ' . $request->getPost()['quality']);
                    $generator->setQuality($request->getPost()['quality']);
                } else {
                    $generator->setQuality(AbstractGenerator::JPEG_QUALITY);
                }

                if(!empty($request->getPost()['page_count'])){
                    $this->io->debug('Set page count: ' . $request->getPost()['page_count']);
                    $generator->setPageCount($request->getPost()['page_count']);
                } else{
                    $generator->setPageRange(AbstractGenerator::PAGE_RANGE);
                }
                $this->io->debug('Page range: ' . $generator->getPageRange());

                if(isset($request->getPost()['filter'])){
                    $this->io->debug('Set post filter: ' . $request->getPost()['filter']);
                    $generator->setFilter($request->getPost()['filter']);
                } else {
                    $generator->setFilter(null);
                }

                $this->io->debug('Start generation');
                $preview = $generator->generate($temp_file);
                if(!$preview){
                    throw new \RuntimeException("Conversion error");
                }
            }
            catch(\Throwable $e){
                return $this->error($response, $e->getMessage());
            }
            finally{
                if($temp_file->getRealPath()){
                    $path = $temp_file->getRealPath();
                    $temp_file = null;
                    //@todo On Windows we have permission denied warning.
                    @unlink($path);
                }
            }

            $this->io->debug('Send preview to client');

            $statusCode = 200;
            $headers = array('Content-Type: application/octet-stream');

            $response->writeHead($statusCode, $headers);
            while(!$preview->eof()){
                $response->write($preview->fread(self::BUFFER_SIZE));
            }
            $response->end();

            if($preview->getRealPath()){
                $path = $preview->getRealPath();
                $preview = null;
                unlink($path);
            }

            $this->io->debug('Client processed at ' . date('h:i:s'));
        });

        $socket->listen($availableServers[$server]['port'], $availableServers[$server]['host']);
        $this->io->success('Preview generator is started on port ' . $availableServers[$server]['port'] . ' on host ' . $availableServers[$server]['host']);

        $this->io->logMemoryUsage();

        try{
            $loop->run();
        }
        catch(\InvalidArgumentException $e){
            $this->io->error("InvalidArgumentException: " . $e->getMessage());
        }

        $this->io->comment('Server is stopped');
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

        if($this->io->isDebug()) {
            $this->io->error($message);
        }

        return false;
    }
}
