<?php
/**
 * Created by PhpStorm.
 * User: Samusevich Alexander
 * Date: 26.03.2017
 * Time: 22:49
 */

namespace Garant\FilePreviewGeneratorBundle\Supervisor;

use Garant\FilePreviewGeneratorBundle\Utils\OutputDecorator;

interface SupervisorInterface
{
    public function run(array $servers, OutputDecorator $io = null);
}