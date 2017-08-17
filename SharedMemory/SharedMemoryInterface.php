<?php

namespace Garant\FilePreviewGeneratorBundle\SharedMemory;

/**
 * Interface SharedMemoryInterface.
 */
interface SharedMemoryInterface
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function set($name, $value);

    /**
     * @param $name
     */
    public function lock($name);

    /**
     * @param $name
     */
    public function unlock($name);
}
