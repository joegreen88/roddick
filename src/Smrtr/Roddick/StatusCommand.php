<?php

namespace Smrtr\Roddick;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check the status of a php web server background process.
 *
 * @package Smrtr\Roddick
 * @author Joe Green <joe.green@smrtr.co.uk>
 */
class StatusCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Check the status of a php web server background process.')
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

        if (file_exists($lockFile) && !PhpServerUtils::getServerStatus($address)) {
            unlink($lockFile);
        }

        if (file_exists($lockFile)) {
            $output->writeln(sprintf("<info>Web server is listening on http://%s</info>", $address));
            if ($docRoot = file_get_contents($lockFile)) {
                $output->writeln(sprintf("Document root:  \"%s\"", $docRoot));
            }
        } else {
            $output->writeln(sprintf("<error>No web server listening on http://%s</error>", $address));
            return 1;
        }
    }
}
