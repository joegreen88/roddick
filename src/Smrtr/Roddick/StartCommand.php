<?php

namespace Smrtr\Roddick;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

/**
 * Start the built-in php web server in the background.
 *
 * @package Smrtr\Roddick
 * @author Joe Green <joe.green@smrtr.co.uk>
 */
class StartCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Start the built-in php web server in the background.')
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
            ->addOption(
                'docroot',
                'd',
                InputOption::VALUE_REQUIRED,
                'Document root for the web server',
                getcwd()
            )
            ->addOption(
                'router',
                'r',
                InputOption::VALUE_REQUIRED,
                'Custom router script'
            )
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!extension_loaded('pcntl')) {
            $output->writeln("<error>The pcntl extension is required to run this command.</error>");
            return 1;
        }

        $address = PhpServerUtils::getServerAddress($input->getArgument("address"), $input->getOption("port"));
        $docRoot = $input->getOption('docroot');
        if (!is_dir($docRoot)) {
            $output->writeln(sprintf("<error>The document root \"%s\" does not exist.</error>", $docRoot));
            return 1;
        }
        $routerScript = $input->getOption("router");
        if (null !== $routerScript && !file_exists($routerScript)) {
            $output->writeln(sprintf("<error>The router script \"%s\" does not exist.</error>", $routerScript));
            return 1;
        }

        $pid = pcntl_fork();

        if ($pid < 0) {
            $output->writeln("<error>Unable to start the server process.</error>");
            return 1;
        }

        if ($pid > 0) {
            $output->writeln(sprintf("<info>Web server listening on http://%s</info>", $address));
            return 0;
        }

        if (posix_setsid() < 0) {
            $output->writeln("<error>Unable to set the child process as session leader</error>");
            return 1;
        }

        if (null === $process = $this->createServerProcess($address, $docRoot, $routerScript)) {
            return 1;
        }

        $process->disableOutput();
        $process->start();
        $lockFile = PhpServerUtils::getLockFile($address);
        file_put_contents($lockFile, $docRoot);
        if (!$process->isRunning()) {
            $output->writeln('<error>Unable to start the server process.</error>');
            unlink($lockFile);
            return 1;
        }
        while ($process->isRunning()) {
            if (!file_exists($lockFile)) {
                $process->stop();
            }
            sleep(1);
        }
    }

    /**
     * Create a process containing the command to run php's built-in web server.
     *
     * @param string            $address
     * @param string            $docRoot
     * @param string|null       $routerScript
     *
     * @return Process
     */
    protected function createServerProcess($address, $docRoot, $routerScript)
    {
        $cmd = sprintf("php -S %s", ProcessUtils::escapeArgument($address));
        if (null !== $routerScript) {
            $cmd .= " " . ProcessUtils::escapeArgument($routerScript);
        }

        return new Process($cmd, $docRoot, null, null, null);
    }
}
