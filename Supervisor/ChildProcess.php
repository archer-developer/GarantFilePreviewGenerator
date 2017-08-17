<?php

/**
 * Created by PhpStorm.
 * User: Samusevich Alexander
 * Date: 26.03.2017
 * Time: 21:57.
 */

namespace Garant\FilePreviewGeneratorBundle\Supervisor;

use Symfony\Component\Process\Process;

/**
 * Class ChildProcess.
 */
class ChildProcess
{
    /**
     * @var string;
     */
    public $server;

    /**
     * @var int;
     */
    public $bornTime;

    /**
     * @var Process
     */
    public $process;
}
