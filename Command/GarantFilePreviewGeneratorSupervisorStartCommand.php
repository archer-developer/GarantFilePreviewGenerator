<?php

namespace Garant\FilePreviewGeneratorBundle\Command;

use Garant\FilePreviewGeneratorBundle\Supervisor\SupervisorInterface;
use Garant\FilePreviewGeneratorBundle\Utils\OutputDecorator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class GarantFilePreviewGeneratorSupervisorStartCommand.
 */
class GarantFilePreviewGeneratorSupervisorStartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('garant:file-preview-generator:supervisor-start')
            ->setDescription('Start supervisor demon')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(-1);

        $io = new OutputDecorator(new SymfonyStyle($input, $cliOutput = $output));

        $servers = $this->getContainer()->getParameter('garant_file_preview_generator.servers');

        $supervisor = $this->getContainer()->getParameter('garant_file_preview_generator.supervisor_class');
        $supervisor = new $supervisor();
        if (!$supervisor instanceof SupervisorInterface) {
            throw new \RuntimeException('Supervisor class must implements SupervisorInterface!');
        }

        $supervisor->setEnvironment($this->getContainer()->getParameter('kernel.environment'));
        $supervisor->run($servers, $io);
    }
}
