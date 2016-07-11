<?php
/**
 * Created by PhpStorm.
 * User: archer
 * Date: 4.6.16
 * Time: 14.58
 */

namespace Garant\FilePreviewGeneratorBundle\Client;

use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;
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

    /**
     * @param \SplFileObject $file
     * @return \SplFileObject
     */
    public function generate(\SplFileObject $file)
    {
        $availableServers = $this->container->getParameter('garant_file_preview_generator.servers');
        $serverNames = array_keys($availableServers);
        $server = $availableServers[$serverNames[rand(0, count($serverNames) - 1)]];

        $client = new Client('http://' . $server['ip'] . ':' . $server['port']);
        $client->setDefaultOption('connect_timeout', $this->container->getParameter('garant_file_preview_generator.remote_client.connect_timeout'));

        $request = $client->post()
            ->addHeader('Content-Type', 'multipart/form-data')
            ->setPostField('out_format', $this->out_format)
            ->setPostField('quality', $this->quality)
            ->setPostField('filter', $this->filter)
            ->setPostField('file_name', $file->getFilename())
            ->addPostFile('file', $file->getRealPath())
        ;

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