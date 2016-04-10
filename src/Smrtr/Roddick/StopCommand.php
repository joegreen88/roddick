<?php

namespace Smrtr\Roddick;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Stop a php built-in web server process that was started with the roddick start command.
 *
 * @package Smrtr\Roddick
 * @author Joe Green <joe.green@smrtr.co.uk>
 */
class StopCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('stop')
            ->setDescription('Stop a php built-in web server process that was started with the roddick start command.')
            ->addArgument(
                'address',
                InputArgument::OPTIONAL,
                "<host>:<port>",
                PhpServerUtils::DEFAULT_HOST
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                'Override the port number of the given address'
            )
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $address = PhpServerUtils::getServerAddress($input->getArgument("address"), $input->getOption("port"));
        $lockFile = PhpServerUtils::getLockFile($address);
        if (!file_exists($lockFile)) {
            $output->writeln(sprintf("<error>No web server is listening on http://%s</error>", $address));
            return 1;
        }
        unlink($lockFile);
        $output->writeln(sprintf("<info>Stopped the web server listening on http://%s</info>", $address));
    }
}
