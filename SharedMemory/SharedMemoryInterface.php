<?php

namespace Garant\FilePreviewGeneratorBundle\SharedMemory;

/**
 * Interface SharedMemoryInterface
 * @package Garant\FilePreviewGeneratorBundle\SharedMemory
 */
interface SharedMemoryInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value);

    /**
     * @param $name
     * @return void
     */
    public function lock($name);

    /**
     * @param $name
     * @return void
     */
    public function unlock($name);
}