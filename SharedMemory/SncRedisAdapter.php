<?php

namespace Garant\FilePreviewGeneratorBundle\SharedMemory;

/**
 * Class SncRedisAdapter
 * @package Garant\FilePreviewGeneratorBundle\SharedMemory
 */
class SncRedisAdapter implements SharedMemoryInterface
{
    /**
     * @var \Predis\Client
     */
    protected $snc_redis;

    public function __construct(\Predis\Client $snc_redis)
    {
        $this->snc_redis = $snc_redis;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {

    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value)
    {

    }

    public function lock($name)
    {
        // TODO: Implement lock() method.
    }

    public function unlock($name)
    {
        // TODO: Implement unlock() method.
    }
}