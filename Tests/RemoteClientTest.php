<?php

/**
 * Created by PhpStorm.
 * User: archer
 * Date: 10.08.2017
 * Time: 21:53
 */

namespace Garant\FilePreviewGeneratorBundle\Tests;

use Garant\FilePreviewGeneratorBundle\Client\RemoteClient;
use Garant\FilePreviewGeneratorBundle\Generator\AbstractGenerator;
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
        sleep(5);
    }

    public function testIndex()
    {
        $file = new \SplFileObject(__DIR__.'/files/test.txt');

        $jpeg_preview_file = $this->client->generate($file, AbstractGenerator::PREVIEW_FORMAT_JPEG);

        $this->assertInstanceOf(\SplFileObject::class, $jpeg_preview_file);

        $data = $jpeg_preview_file->fread(1024);

        $this->assertGreaterThan(0, strlen($data));
    }

    public function testCustomFormat()
    {
        $file = new \SplFileObject(__DIR__.'/files/test.docx');

        $text_preview_file = $this->client->generate($file, AbstractGenerator::PREVIEW_FORMAT_TEXT);

        $this->assertInstanceOf(\SplFileObject::class, $text_preview_file);

        $data = $text_preview_file->fread(1024);

        $this->assertGreaterThan(0, strlen($data));
        $this->assertEquals(trim($data), 'Test DOCX');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUnsupportedFormat()
    {
        $file = new \SplFileObject(__DIR__.'/files/test.rar');

        $this->client->generate($file, AbstractGenerator::PREVIEW_FORMAT_TEXT);
        $this->assertEquals($this->serverProcess->getStatus(), Process::STATUS_STARTED);
    }
}