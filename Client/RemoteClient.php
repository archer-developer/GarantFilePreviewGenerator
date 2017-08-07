<?php
/**
 * Created by PhpStorm.
 * User: Alexander Samusevich
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Client;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;
use Garant\FilePreviewGeneratorBundle\SharedMemory\SharedMemoryInterface;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\Response;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class RemoteClient
 * @package Garant\FilePreviewGeneratorBundle\Client
 */
class RemoteClient extends AbstractGenerator
{
    use ContainerAwareTrait;

    const BUFFER_SIZE = 262144; // 256Kb

    const SELECT_ALGORITHM_RAND = 'random';
    const SELECT_ALGORITHM_ROUND_ROBIN = 'round_robin';

    /**
     * @inheritdoc
     */
    public function support(\SplFileObject $file, $out_format): bool
    {
        return true;
    }

    /**
     * @param \SplFileObject $file - input file
     * @param string $out_format
     * @return \SplFileObject - file preview
     */
    public function generate(\SplFileObject $file, $out_format)
    {
        $availableServers = $this->container->getParameter('garant_file_preview_generator.servers');
        $selectAlgorithm = $this->container->getParameter('garant_file_preview_generator.server_select_algorithm');

        // Select server
        $server = null;
        switch($selectAlgorithm){
            case self::SELECT_ALGORITHM_RAND:

                $serverNames = array_keys($availableServers);
                $server = $availableServers[$serverNames[rand(0, count($serverNames) - 1)]];
                break;

            case self::SELECT_ALGORITHM_ROUND_ROBIN:

                $shm_service = $this->container->getParameter('garant_file_preview_generator.shared_memory');
                $shm = $this->container->get($shm_service);
                if(!$shm instanceof SharedMemoryInterface){
                    throw new \RuntimeException('Configuration error: Service '.get_class($shm).' must implement SharedMemory interface!');
                }

                //@todo select server

                break;

            default:
                throw new \RuntimeException('Invalid select algorithm: ' . $selectAlgorithm . '. See available server selection algorithm');
        }

        if(!$server){
            throw new \RuntimeException('No servers available!');
        }

        // Maximum connection timeout in seconds
        $timeout = $this->container->getParameter('garant_file_preview_generator.remote_client.connect_timeout');

        // Configure Guzzle HTTP client
        $client = new Client($server['protocol'] . '://' . $server['host'] . ':' . $server['port']);
        $client->setDefaultOption('connect_timeout', $timeout);

        $request = $client->post()
            ->addHeader('Content-Type', 'multipart/form-data')
            ->setPostField('out_format', $out_format)
            ->setPostField('quality', $this->quality)
            ->setPostField('file_name', $file->getFilename())
            ->addPostFile('file', $file->getRealPath())
        ;
			
		if(!empty($this->filter)){
			$request->setPostField('filter', $this->filter);
		}
        if(!empty($this->page_range)) {
            $request->setPostField('page_range', $this->page_range);
        }

        /**
         * @var Response $response
         */
        try{
            $response = $request->send();
        }
        catch(BadResponseException $e){
            throw new \RuntimeException($e->getMessage());
        }

        if($response->getStatusCode() != 200){
            throw new \RuntimeException($response->getBody(true));
        }

        $preview = new \SplTempFileObject();
        $response->getBody()->rewind();
        while(!$response->getBody()->feof()){
            $b = $response->getBody()->read(self::BUFFER_SIZE);
            $preview->fwrite($b);
        }
        $preview->rewind();

        return $preview;
    }
}