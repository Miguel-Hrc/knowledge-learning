<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Symfony console command to reset the relational test database.
 *
 * This command drops the test database, recreates it,
 * runs migrations, and prepares it for testing.
 *
 * @package App\Command
 */
class ResetTestDatabaseCommand extends Command
{
    /**
     * The default command name used in the CLI.
     *
     * @var string
     */
    protected static $defaultName = 'test:reset';

    /**
     * The default command description shown in help output.
     *
     * @var string
     */
    protected static $defaultDescription = 'Resets the test database (drop, create, migrate, fixtures)';

    /**
     * Constructor.
     *
     * Initializes the command with its name.
     */
    public function __construct()
    {
        parent::__construct('test:reset');
    }

    /**
     * Executes the command.
     *
     * Runs a series of Symfony CLI commands to reset the test database:
     * - Drops the database
     * - Creates a new one
     * - Applies migrations
     *
     * @param InputInterface $input The input interface
     * @param OutputInterface $output The output interface
     * @return int Command::SUCCESS on success, Command::FAILURE on error
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $commands = [
            ['php', 'bin/console', 'doctrine:database:drop', '--env=test', '--force'],
            ['php', 'bin/console', 'doctrine:database:create', '--env=test'],
            ['php', 'bin/console', 'doctrine:migrations:migrate', '--env=test', '--no-interaction'],
        ];

        foreach ($commands as $cmd) {
            $process = new Process($cmd);
            $process->run();

            if (!$process->isSuccessful()) {
                $io->error("❌ Command failed: " . implode(' ', $cmd));
                $io->writeln($process->getErrorOutput());
                return Command::FAILURE;
            }

            $io->success("✔ " . implode(' ', $cmd));
        }

        $io->success('✅ Test database successfully reset.');
        return Command::SUCCESS;
    }
}