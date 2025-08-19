<?php

namespace App\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Symfony console command to reset the MongoDB test database.
 * 
 * This command drops the current test database, recreates the collections,
 * and forces schema creation by inserting and removing a dummy document.
 * 
 * @package App\Command
 */
class ResetMongoTestDatabaseCommand extends Command
{
    /**
     * The default command name used in the CLI.
     *
     * @var string
     */
    protected static $defaultName = 'test:reset-mongo';

    /**
     * The default command description shown in help output.
     *
     * @var string
     */
    protected static $defaultDescription = 'Resets the MongoDB test database (drop, create, schema)';

    /**
     * The Doctrine MongoDB DocumentManager instance.
     *
     * @var DocumentManager|null
     */
    private ?DocumentManager $dm;

    /**
     * Constructor.
     *
     * @param DocumentManager|null $dm The MongoDB DocumentManager
     */
    public function __construct(?DocumentManager $dm)
    {
        parent::__construct('test:reset-mongo');
        $this->dm = $dm;
    }

    /**
     * Executes the command.
     *
     * Drops the current MongoDB test database, recreates collections,
     * and forces schema creation by inserting and removing a dummy document.
     *
     * @param InputInterface $input The input interface
     * @param OutputInterface $output The output interface
     * @return int Command::SUCCESS on success, Command::FAILURE on error
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $dbName = $this->dm->getConfiguration()->getDefaultDB();
            $client = $this->dm->getClient();
            $client->dropDatabase($dbName);
            $io->success("✔ MongoDB database '$dbName' dropped");

            $this->dm->getSchemaManager()->createCollections();

            $dummy = new \App\Document\LessonDocument();
            $dummy->setTitle('Dummy');
            $dummy->setContent('Temp');
            $dummy->setPrice(0.0);

            $this->dm->persist($dummy);
            $this->dm->flush();
            $this->dm->remove($dummy);
            $this->dm->flush();

            $io->success('✔ Dummy document inserted and removed to trigger schema creation');
            $io->success('✔ MongoDB collections recreated');
            $io->note('Using MongoDB database: ' . $dbName);
            $io->success('✅ MongoDB test database successfully reset.');
            return Command::SUCCESS;
        } 
        catch (\Exception $e) {
            $io->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}