<?php

/**
 * Created by PhpStorm.
 * User: archer
 * Date: 10.08.2017
 * Time: 21:53
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Client\RemoteClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

class RemoteClientTest extends KernelTestCase
{
    /**
     * @var RemoteClient
     */
    private $client;
    /**
     * @var Process
     */
    private $serverProcess;

    public function setUp()
    {
        static::bootKernel();

        $this->client = self::$kernel->getContainer()->get('garant_file_preview_generator.remote_client');

        $this->serverProcess = new Process('php bin/console garant:file-preview-generator:server-start test -vvv --env=test');
        $this->serverProcess->start();

        $this->assertEquals($this->serverProcess->getStatus(), Process::STATUS_STARTED);
    }

    public function testIndex()
    {

    }
}